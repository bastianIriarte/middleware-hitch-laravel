<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Services\BillService;
use App\Services\PaymentsService;
use App\Services\SapServiceLayerService;
use App\Services\SqlPosService;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Boletas",
 *     description="Integración de boletas de POS a SAP"
 * )
 */
class BillController extends Controller
{

    /**
     * @OA\Post(
     *     path="/boletas-pos/integrar",
     *     tags={"Boletas"},
     *     summary="Procesar boletas desde POS hacia SAP",
     *     description="Recibe filtros opcionales en el body para consultar boletas pendientes en POS y generar boletas (Invoice) en SAP.",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="docnum", type="integer", example=123, description="Número de boleta específico"),
     *             @OA\Property(property="docdate", type="string", format="date", example="2025-08-25", description="Fecha exacta de la boleta (YYYY-MM-DD)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consulta ejecutada exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Consulta ejecutada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="docNum", type="string", example="8"),
     *                     @OA\Property(
     *                         property="response",
     *                         type="object",
     *                         @OA\Property(property="DocEntryReceipt", type="integer", example=104),
     *                         @OA\Property(property="DocNumReceipt", type="integer", example=48),
     *                         @OA\Property(property="DocEntryInvoice", type="integer", example=1616),
     *                         @OA\Property(property="DocNumInvoice", type="integer", example=155)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-08-25T16:44:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al ejecutar consulta",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al ejecutar consulta: descripción del error"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-08-25T16:45:00-04:00")
     *         )
     *     )
     * )
     */

    public function index(Request $request): JsonResponse
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        $oinvHeaderTable =  config('pos.TABLES.OINV_HEADER');

        try {
            Log::info("SE INICIA PROCESO DE SOLICITUD DE BOLETAS: " . now());
            #INSTANCIAMOS SERVICIO PARA UTILIZAR BASE DE DATOS DEL POS
            $sqlPosService = new SqlPosService();

            #ARMAMOS QUERY PARA EXTRAER LAS BOLETAS 
            $query = 'SELECT * FROM [dbo].[' . $oinvHeaderTable . '] WHERE DocEntry IS NULL';
            // $query = 'SELECT * FROM [dbo].[' . $oinvHeaderTable . '] WHERE 1 = 1';

            #Filtro por DocNum
            if ($request->filled('docnum')) {
                $docnum = intval($request->input('docnum'));
                $query .= " AND DocNum IN ($docnum)";
            }

            #Filtro por fecha exacta
            if ($request->filled('docdate')) {
                $docdate = $request->input('docdate');
                $docdate = str_replace('-', '', $docdate);
                $query .= " AND DocDate = '{$docdate}'";
            }

            $query .= " ORDER BY DocNum ASC";

            #OBTENEMOS TODOS LAS BOLETAS GENERADAS POR DÍA
            $getDataHeader = $sqlPosService->executeQuery($query);
            if (!$getDataHeader['success']) {
                throw new Exception($getDataHeader['error']);
            }

            #SI NO EXISTEN DATOS CORTAMOS EJECUCIÓN
            if (count($getDataHeader['data']) == 0) {
                return ApiResponse::success([], 'Consulta ejecutada exitosamente', 200);
            }

            $generalResponse = [];

            #SE RECORREN LAS BOLETAS PARA REGISTRAR DE MANERA UNITARIA EN SAP
            foreach ($getDataHeader['data'] as $key => $headerData) {
                Log::info("SE INICA PROCESO DE BOLETA {$headerData->DocNum}: " . now());
                if (empty($headerData)) continue;
                $integrationLog = IntegrationLogger::create(
                    'bill_invoices',
                    [
                        'service_name' => 'Crear',
                        'destiny' => 'SAP',
                        'status_integration_id' => 1,
                    ]
                );

                if (!$integrationLog['result']) {
                    return ApiResponse::error(
                        "Error al enviar boleta: {$integrationLog['message']}",
                        [],
                        500
                    );
                }
                #INSTANCIAMOS SERVICIO PARA BOLETAS
                $billService = new BillService();
                $result = $billService->generateBill($headerData);

                $responseDocNum = [
                    'success' => false,
                    'docNum' => $headerData->DocNum,
                ];
                if (!$result['success']) {
                    $responseDocNum['error'] =  json_encode($result['error']);
                    IntegrationLogger::update('bill_invoices', $integrationLog['data']->id, [
                        'code' => 500,
                        'entry_request_body' => $result['logs']['createBodyReceipt'] ?? null,
                        'entry_response'     => $result['logs']['responseReceipt'] ?? null,
                        'request_body'       => $result['logs']['createBodyBill'] ?? null,
                        'message'            => json_encode($responseDocNum),
                        'status_integration_id' => 4,
                    ]);
                } else {
                    $responseDocNum['success'] =  true;
                    $responseDocNum['response'] = $result['response'];
                    IntegrationLogger::update('bill_invoices', $integrationLog['data']->id, [
                        'code' => 201,
                        'entry_request_body' => $result['logs']['createBodyReceipt'] ?? null,
                        'entry_response'     => $result['logs']['responseReceipt'] ?? null,
                        'request_body'       =>  $result['logs']['createBodyBill'] ?? null,
                        'response'           => json_encode($result['response']),
                        'message'            => "Boleta creada correctamente",
                        'status_integration_id' => 3,
                    ]);
                }

                $generalResponse[] = $responseDocNum;
            }
            Log::info("SE FINALIZA PROCESO DE SOLICITUD DE BOLETAS: " . now());

            return ApiResponse::success($generalResponse, 'Consulta ejecutada exitosamente', 200);
        } catch (\Exception $e) {

            Log::error('Error al ejecutar consulta [SQL POS]: ' . $e->getMessage());
            Log::info("FIN POR ERROR PROCESO DE SOLICITUD DE BOLETAS: " . now());

            return ApiResponse::error(
                'Error al ejecutar consulta: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    public function generatePayment(Request $request): JsonResponse
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        $orctHeaderTable =  config('pos.TABLES.ORCT_HEADER'); // Cabecera pagos
        $orctDetailTable =  config('pos.TABLES.ORCT_DETAIL'); // Detalle pagos
        $orctTCRTable = config('pos.TABLES.ORCT_TCR');

        try {
            Log::info("SE INICIA PROCESO DE PAGO DE BOLETAS: " . now());
            #INSTANCIAMOS SERVICIO PARA UTILIZAR BASE DE DATOS DEL POS
            $sqlPosService = new SqlPosService();

            #ARMAMOS QUERY PARA EXTRAER LOS PAGOS
            $query = 'SELECT * FROM [dbo].[' . $orctHeaderTable . '] WHERE DocEntry IS NULL';
            // $query = 'SELECT * FROM [dbo].[' . $orctHeaderTable . '] WHERE 1 = 1';

            #Filtro por DocNum
            if ($request->filled('docnum')) {
                $docnum = intval($request->input('docnum'));
                $query .= " AND DocNum IN ($docnum)";
            }

            // #Filtro por fecha exacta
            // if ($request->filled('docdate')) {
            //     $docdate = $request->input('docdate');
            //     $docdate = str_replace('-', '', $docdate);
            //     $query .= " AND DocDate = '{$docdate}'";
            // }

            $query .= " ORDER BY DocNum ASC";
            #OBTENEMOS TODOS LOS PAGOS
            $getDataHeader = $sqlPosService->executeQuery($query);

            // pre_die($getDataHeader);

            if (!$getDataHeader['success']) {
                throw new Exception($getDataHeader['error']);
            }

            #SI NO EXISTEN DATOS CORTAMOS EJECUCIÓN
            if (count($getDataHeader['data']) == 0) {
                return ApiResponse::success([], 'Consulta ejecutada exitosamente', 200);
            }

            $generalResponse = [];

            #SE RECORREN LOS PAGOS PARA REGISTRAR DE MANERA UNITARIA EN SAP
            foreach ($getDataHeader['data'] as $key => $headerData) {
                Log::info("SE INICA PROCESO DE PAGO {$headerData->DocNum}: " . now());
    
               if (empty($headerData)) continue;

                // Validar si pago tiene boleta asociada a traves de detalle
                $getDataDetail = $sqlPosService->executeQuery('SELECT * FROM [dbo].[' . $orctDetailTable . '] WHERE ParentKey = :parentkey', [
                    'parentkey' => $headerData->DocNum
                ]);

                // Si no tiene detalle asociador continuar
                if (empty($getDataDetail['data'])) continue;
                // Si no tiene boleta asociada continuar
                if (empty($getDataDetail['data'][0]->DocEntry)) continue;

                // Obtener data TCR
                $getDataTCR = $sqlPosService->executeQuery('SELECT * FROM [dbo].[' . $orctTCRTable . '] WHERE ParentKey = :parentkey', [
                    'parentkey' => $headerData->DocNum
                ]);

                $integrationLog = IntegrationLogger::create(
                    'payments',
                    [
                        'service_name' => 'Crear',
                        'destiny' => 'SAP',
                        'status_integration_id' => 1,
                    ]
                );

                if (!$integrationLog['result']) {
                    Log::error("Error al generar log de pago: {$integrationLog['message']}");
                    continue;
                }

                $responseDocNum = [
                    'success' => false,
                    'docNum' => $headerData->DocNum,
                ];

                // Instanciar servicio pagos
                $paymentService = new PaymentsService();
                // Formatear data para pago
                $billDocEntry = $getDataDetail['data'][0]->DocEntry;
                $paymentDocDate = isset($headerData->DocDate) && !empty($headerData->DocDate) ? new DateTime($headerData->DocDate) : '';
                $formattedPaymentDocDate = !empty($paymentDocDate) ? $paymentDocDate->format('Y-m-d') : '';

                $paymentDueDate = isset($headerData->DueDate) && !empty($headerData->DueDate) ? new DateTime($headerData->DueDate) : '';
                $formattedPaymentDueDate = !empty($paymentDueDate) ? $paymentDueDate->format('Y-m-d') : '';
            
                
                $creditSUm = 0;
                $creditData = [];
                if (!empty($getDataTCR['data'])) {
                    
                    foreach ($getDataTCR['data'] as $d) {
                        $line = [
                            'LineNum'              => $d->LineNum ?? null,
                            'CreditCard'           => $d->CreditCard ?? null,
                            'CreditAcct'           => $d->CreditAcct ?? null,
                            'CreditCardNumber'     => $d->CreditCardNumber ?? null,
                            'CardValidUntil'       => $d->CardValidUntil ?? null,
                            'VoucherNum'           => $d->VoucherNum ?? null,
                            'OwnerIdNum'           => $d->OwnerIdNum ?? null,
                            'OwnerPhone'           => $d->OwnerPhone ?? null,
                            'PaymentMethodCode'    => $d->PaymentMethodCode ?? null,
                            'NumOfPayments'        => $d->NumOfPayments ?? null,
                            'FirstPaymentDue'      => $d->FirstPaymentDue ?? null,
                            'FirstPaymentSum'      => $d->FirstPaymentSum ?? null,
                            'AdditionalPaymentSum' => $d->AdditionalPaymentSum ?? null,
                            'CreditSum'            => $d->CreditSum ?? null,
                            'CreditCur'            => !empty($d->CreditCur) ? $d->CreditCur : 'CLP',
                            'CreditRate'           => $d->CreditRate ?? null,
                            'ConfirmationNum'      => $d->ConfirmationNum ?? null,
                            'NumOfCreditPayments'  => $d->NumOfCreditPayments ?? null,
                            'CreditType'           => $d->CreditType ?? null,
                            // 'SplitPayments'        => $d->SplitPayments ?? null,
                            'SplitPayments'        => 'tNO',
                        ];

                        $creditSUm += $d->CreditSum;
                        $creditData[] = $line;
                    }
                }

                $sumApplied = $creditSUm + $headerData->CashSum;

                $data = [
                    'CardCode'       => $headerData->CardCode ?? '',
                    'DocDate'        => $formattedPaymentDocDate,
                    'DueDate'        => $formattedPaymentDueDate,
                    'DocType'        => $headerData->DocType ?? '',
                    // 'Remarks'        => "Pago generado desde middleware Hitch | Boleta: $billDocEntry | Caja: $headerData->U_NUMCAJA | Local: $headerData->U_LOCAL",
                    'Remarks'        => "Caj: $headerData->U_NUMCAJA | Loc: $headerData->U_LOCAL",
                    'CashAccount'    => $headerData->CashAccount,
                    // 'CashAccount'    => '1011100090',
                    'CashSum'        => $headerData->CashSum,
                    'lines'          => [
                        [
                            'DocEntry'      => $billDocEntry,
                            'InvoiceType'   => 'it_Invoice',
                            'SumApplied'    => $sumApplied,
                        ]
                    ],
                    'credit_cards' => $creditData
                ];

                // pre_die($data);

                $paymentDocEntry = null;
                $paymentErrors = null;

                try {
                    
                    $newPayment = $paymentService->prepareCreateData($data);

                    // pre_die($newPayment);

                    IntegrationLogger::update('payments', $integrationLog['data']->id, [
                        'origin' => "INTEGRACIÓN FMMS",
                        'create_body' => json_encode($newPayment),
                        'attempts' => 1,
                        'status_integration_id' => 2
                    ]);
                    
                    // Enviar pago a sap
                    $response = $paymentService->sendData($newPayment);

                    // Actualizar DocEntry del pago en caso de exito
                    $paymentDocEntry = $response['response']['DocEntry'];
                    $paymentDocNum = $response['response']['DocNum'];


                    IntegrationLogger::update('payments', $integrationLog['data']->id, [
                        'code' => 201,
                        'message' => 'Pago creado exitosamente',
                        'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                        'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                        'status_integration_id' => 3
                    ]);


                    $responseDocNum['success'] =  true;
                    $responseDocNum['Payment_DocEntry'] =  $paymentDocEntry;
                    $responseDocNum['Payment_DocNum'] =  $paymentDocNum;

                    $generalResponse[] = $responseDocNum;
                } catch (\Exception $e) {
                    $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

                    Log::error('Error al crear pago: ' . $formattedException->message, [
                        'request_data' => $formattedException->request,
                        'user_id' => auth()->id(),
                        'timestamp' => now()
                    ]);

                    // Usar el manejador de errores mejorado
                    \App\Services\SapErrorHandlerService::setRequestContext($data);
                    $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                        $formattedException->message,
                        $e->getCode()
                    );

                    $paymentErrors = json_encode($errorDetails);

                    $responseDocNum['success'] = false;
                    $responseDocNum['error'] = $errorDetails['technical_details']['original_message'] ?? $errorDetails['user_message'];
                    $generalResponse[] = $responseDocNum;

                    IntegrationLogger::update('payments', $integrationLog['data']->id, [
                        'code' => $errorDetails['status_code'] ?? 400,
                        'request_body' => $formattedException->request ?? json_encode($formattedException->request, JSON_UNESCAPED_UNICODE),
                        'message' => $errorDetails['user_message'],
                        'response' => $errorDetails,
                        'status_integration_id' => 4,
                    ]);
                }

                // Actualizar cabecera de pago en POS
                $updateCab = $sqlPosService->executeStatement('UPDATE [dbo].[' . $orctHeaderTable . '] SET Errors = :errors, DocEntry = :docentry WHERE DocNum = :docnum', [
                    'errors' => $paymentErrors,
                    'docnum' => $headerData->DocNum,
                    'docentry' => $paymentDocEntry,
                ]);
                
            }
            Log::info("SE FINALIZA PROCESO DE SOLICITUD DE PAGOS: " . now());

            return ApiResponse::success($generalResponse, 'Consulta ejecutada exitosamente', 200);
        } catch (\Exception $e) {

            Log::error('Error al ejecutar consulta [SQL POS]: ' . $e->getMessage());
            Log::info("FIN POR ERROR PROCESO DE SOLICITUD DE PAGOS: " . now());

            return ApiResponse::error(
                'Error al ejecutar consulta: ' . $e->getMessage(),
                [],
                500
            );
        }
    }
}
