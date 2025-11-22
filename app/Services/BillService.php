<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Requests\Sap\ArticleStoreRequest;
use App\Http\Requests\Sap\ArticleUpdateRequest;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BillService
{
    protected $sapService;

    public $createBodyReceipt;
    public $createBodyBill;
    public $responseReceipt;
    public $modeTest = FALSE;
    public $tableHeader;
    public $tableDetail;
    public $tablePaymentDetails;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
        $this->createBodyReceipt = null;
        $this->createBodyBill    = null;
        $this->responseReceipt   = null;
        $this->tableHeader   = config('pos.TABLES.OINV_HEADER');
        $this->tableDetail   = config('pos.TABLES.OINV_DETAIL');
        $this->tablePaymentDetails = config('pos.TABLES.ORCT_DETAIL');
    }


    public function generateBill($headerData,  $retry = false, $docEntryEntrada = 0, $docNumEntrada = 0): array
    {
        #INSTANCIAMOS SERVICIO PARA UTILIZAR BASE DE DATOS DEL POS
        $sqlPosService = new SqlPosService();

        /*
            1) VALIDAMOS STOCK DE PRODUCTOS POR BODEGA OITW + OITM (SI EXISTE DIFERENCIA SE DEBE CREAR ENTRADA) 
            2) OBTENEMOS EL LISTADO DE PRODUCTOS VENDIDOS POR POS
        */
        $validateStock = $this->validateStock($headerData->DocNum, $sqlPosService, $retry);
        if (!$validateStock['success']) {
            return $this->returnError($validateStock['error'], $validateStock['connection'], $validateStock['error_connection']);
        }

        #GUARDA PRODUCTOS A RECEPCIONAR
        $dataReceipt = $validateStock['dataReceipt'];


        #GUARDA LISTADO DE PRODUCTOS VENDIDOS POR POS ([SAP-OINV-DET])
        $getDataDetail = $validateStock['getDataDetail'];

        #************************************#
        #*** SE CREA ENTRADA OPCIONALMENTE **#
        #************************************#
        if (!empty($dataReceipt)) {
            $generateReceipt = $this->generateReceipt($dataReceipt, $headerData, $sqlPosService);
            if (!$generateReceipt['success']) {

                return $this->returnError(json_encode($generateReceipt['error']), $generateReceipt['connection'], $generateReceipt['error_connection']);
            }

            #GUARDAMOS LOS DOCENTRY Y DOCNUM DE LA ENTRADA DE MERCADERIA
            $docEntryEntrada = $generateReceipt['docEntry'];
            $docNumEntrada = $generateReceipt['docNumSAP'];

            #ESPERAMOS 3 SEGUNDOS ANTES DE CONTINUAR PARA EVITAR ERROR EN GENERACIÓN DE BOLETA POR FALTA DE STOCK
            sleep(3);

            #SE VALIDAR STOCK NUEVAMENTE PARA EVITAR DIFERENCIAS TRAS ENTRADA DE MERCADERIA
            $validateStock = $this->validateStock($headerData->DocNum, $sqlPosService, $retry);
            if (!$validateStock['success']) {
                return $this->returnError($validateStock['error'], $validateStock['connection'], $validateStock['error_connection']);
            }

            #SI TRAS ENTRADA AUN EXISTE DIFERENCIA DE PRODUCTOS A RECEPCIONAR SE REINTENTA 1 VEZ MÁS
            $dataReceipt = $validateStock['dataReceipt'];
            if (!empty($dataReceipt)) {
                if ($retry) {
                    return $this->returnError("Error al validar existencia de productos para entrada de mercadería. Numero de intentos: 2 | Productos con diferencias: " . count($dataReceipt) . " | Detalle: " . json_encode($dataReceipt));
                } else {
                    return $this->generateBill($headerData,  true, $docEntryEntrada, $docNumEntrada);
                }
            }
        }
        #***** FIN DE ENTRADA *******#


        #*************************************************#
        #********* CREACIÓN DE LA BOLETA EN SAP **********#
        #*************************************************#

        #SE FORMATEA DATA CON REGISTROS DE LA BASE DE DATOS POS
        $formattedDataFromPos = $this->prepareItemData($headerData, $getDataDetail);

        $purchaseInvoicesService = new PurchaseInvoicesService();

        #SE FORMATEA LA DATA PARA QUE TENGA EL FORMATO ESPERADO EN LA INVOICE
        $formatedBillData = $purchaseInvoicesService->prepareReserveInvoiceData($formattedDataFromPos, 'BILL');
        Log::info(["payload Bill" => $formatedBillData]);

        $tableHeader = $this->tableHeader;
        $tablePaymentDetails = $this->tablePaymentDetails;
        try {
            #GUARDAMOS BODY PARA LOGS
            $this->createBodyBill = json_encode($formatedBillData);

            $sendInvoice = $this->sapService->post('/Invoices', $formatedBillData);

            #GUARDAMOS LOS DOCENTRY Y DOCNUM DE LA BOLETA
            $docEntry = $sendInvoice['response']['DocEntry'];
            $docNumSAP = $sendInvoice['response']['DocNum'];

            #ACTUALIZAMOS EL DOCENTRY EN LA BASE DEL POS
            $updateCab = $sqlPosService->executeStatement(
                'UPDATE [dbo].[' . $tableHeader . '] SET DocEntry = :docEntry, Errors = :errors WHERE DocNum = :docnum',
                [
                    'docEntry' => $docEntry,
                    'docnum' => $headerData->DocNum,
                    'errors' => null
                ]
            );
            # ACTUALIZAR DETALLES DEL PAGO EN BASE POS
            $updatePaymentDet = $sqlPosService->executeStatement('UPDATE [dbo].[' . $tablePaymentDetails . '] SET DocEntry = :docentry WHERE ParentKey = :parentkey', [
                'docentry' => $docEntry,
                'parentkey' => $headerData->DocNum
            ]);

            //INFORMACIÓN: LA CABECERA DEL OCRT ES SOLO PARA COMPLETAR EL PAGO EJ. DocDate, AccountCode, etc.

            return [
                'success' => true,
                'response' => [
                    'DocEntryReceipt' => $docEntryEntrada,
                    'DocNumReceipt' => $docNumEntrada,
                    'DocEntryInvoice' => $docEntry,
                    'DocNumInvoice' => $docNumSAP,
                ],
                'logs' => [
                    'createBodyReceipt' => $this->createBodyReceipt,
                    'createBodyBill'    => $this->createBodyBill,
                    'responseReceipt'   => $this->responseReceipt,
                ]

            ];
        } catch (Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al crear boleta: $headerData->DocNum" . $formattedException->message);
            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            $tableHeader = $this->tableHeader;
            #EN CASO DE ERROR EN PROCESO SE GUARDA EN BASE DE DATOS DE POS
            $updateCab = $sqlPosService->executeStatement('UPDATE [dbo].[' . $tableHeader . '] SET Errors = :errors, DocEntry = :docentry WHERE DocNum = :docnum', [
                'errors' => json_encode($errorDetails),
                'docnum' => $headerData->DocNum,
                'docentry' => null,
            ]);

            return $this->returnError(json_encode($errorDetails), $updateCab['success'], $updateCab['error']);
        }
    }

    public function prepareItemData(object $headerData, array $detailData): array
    {
        $details =  $detailData['data'];
        $details = array_map(function ($line) {
            $line = (array)$line;
            $line['PriceAfterVAT']        = $line['PriceAfterVAT'];
            $line['WhsCode']      = trim($line['WarehouseCode']);
            $line['AccountCode']  = $this->modeTest ? '7021100030' : trim($line['AccountCode']);
            $line['OcrCode']      = $this->modeTest ? 'SANGOL' : trim($line['CostingCode']);  #CostingCode = SUCURSAL (CC1 EN POS)
            $line['OcrCode2']      = $this->modeTest ? 'AANGOL' : trim($line['CostingCode2']); #CostingCode2 = AREA (CC4 EN POS)
            $line['OcrCode3']     = $this->modeTest ? NULL : trim($line['CostingCode3']); #CostingCode3 = CANAL DE VENTA (CC3 EN POS)
            return $line;
        }, $details);

        $today = date('Y-m-d');

        $local = trim($headerData->U_LOCAL);

        $fieldMapping = [
            'CardCode'   => $this->modeTest ? 'CCL77184618' : $headerData->CardCode,
            'DocDate'    => !empty($headerData->DocDate) ? ordenar_fechaServidor($headerData->DocDate) : $today,
            'DocDueDate' => !empty($headerData->DocDueDate) ? ordenar_fechaServidor($headerData->DocDueDate) : $today,
            'TaxDate'    => !empty($headerData->DocDate) ? ordenar_fechaServidor($headerData->DocDate) : $today,
            'lines'      => [],
            'Comments'   => "Boleta generada desde middleware Hitch | Caja: $headerData->U_NUMCAJA | Local: $local"
        ];


        #SE AGREGAN LINEAS PARA BOLETA
        foreach ($details as $d) {
            $fieldMapping['lines'][] = collect($d)->toArray();
        }
        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields($headerData, $detailData);

        $data = array_merge($fieldMapping, $arrUdf);
        return $data;
    }

    public function userDefinedFields($headerData, $data = [])
    {
        return [
            'U_INTEGRACION'  => "S",
            'U_LOCAL'  => $headerData->U_LOCAL ?? "",
            'U_NUMCAJA'  => $headerData->U_NUMCAJA ?? "",
        ];
    }


    private function generateReceipt(array $dataReceipt, object $headerData,  $sqlPosService)
    {
        try {

            #INSTANCIAMOS SERVICIO PARA UTILIZAR LA ENTRADA DE MERCADERIA
            $goodsReceiptService = new GoodsReceiptService();

            #SE FORMATEA DATA CON REGISTROS DE LA BASE DE DATOS POS
            $formattedDataFromPos = $this->prepareItemDataReceipt($headerData, $dataReceipt);


            #SE FORMATEA LA DATA PARA QUE TENGA EL FORMATO ESPERADO EN LA ENTRADA DE MERCADERIA
            $formattedData = $goodsReceiptService->prepareCreateData($formattedDataFromPos);
            #GUARDAMOS BODY PARA LOGS
            $this->createBodyReceipt = json_encode($formattedData);

            Log::info(["payload Receipt" => $formattedData]);
            $response = $goodsReceiptService->sendData($formattedData);
            #GUARDAMOS LOS DOCENTRY Y DOCNUM DE LA BOLETA
            $docEntry = $response['response']['DocEntry'];
            $docNumSAP = $response['response']['DocNum'];

            #GUARDAMOS RESPONSE PARA LOGS
            $this->responseReceipt = json_encode($response['response']);

            #RETORNAMOS REGISTROS
            return [
                'success' => true,
                'docEntry' => $docEntry,
                'docNumSAP' => $docNumSAP,
            ];
        } catch (Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());
            Log::error("Error al crear entrada de mercadería: $headerData->DocNum" . $formattedException->message);


            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            $tableHeader = $this->tableHeader;
            #EN CASO DE ERROR EN PROCESO SE GUARDA EN BASE DE DATOS DE POS
            $updateCab = $sqlPosService->executeStatement('UPDATE [dbo].[' . $tableHeader . '] SET Errors = :errors, DocEntry = :docentry WHERE DocNum = :docnum', [
                'errors' => json_encode($errorDetails),
                'docnum' => $headerData->DocNum,
                'docentry' => null,
            ]);

            return [
                'success' => false,
                'error' => $errorDetails,
                'connection' => $updateCab['success'],
                'error_connection' => $updateCab['error']
            ];
        }
    }

    /**
     * Prepara la data de una Entrada de Mercadería (Goods Receipt) para enviarla a SAP.
     *
     * Esta función toma la cabecera de la boleta y las líneas de productos que deben 
     * recepcionarse (por diferencias de stock), y devuelve un arreglo formateado con 
     * la estructura esperada por el Service Layer de SAP.
     *
     * @param object $headerData 
     *   Objeto con los datos de cabecera de la boleta desde el POS. 
     *   Campos relevantes utilizados:
     *     - U_NUMCAJA : Número de caja que generó la boleta.
     *     - U_LOCAL   : Local asociado a la boleta.
     *
     * @param array $dataReceipt 
     *   Arreglo de líneas de productos a recepcionar. 
     *   Cada línea corresponde a un item de la boleta e incluye, entre otros:
     *     - ItemCode      : Código de producto.
     *     - Quantity      : Cantidad a recepcionar.
     *     - AccountCode   : Código de cuenta contable (se normaliza con trim()).
     *     - WarehouseCode : Código de bodega (se normaliza con trim()).
     *     - CostingCode   : Sucursal (POS: CC1).
     *     - CostingCode2  : Área (POS: CC4).
     *     - CostingCode3  : Canal de venta (POS: CC3).
     *
     * @return array
     *   Arreglo formateado con la siguiente estructura:
     *   [
     *     'DocDate'    => 'YYYY-MM-DD', // Fecha actual
     *     'DocDueDate' => 'YYYY-MM-DD', // Fecha actual
     *     'TaxDate'    => 'YYYY-MM-DD', // Fecha actual
     *     'Reference2' => 'AuditPOS',
     *     'Comments'   => 'Entrada generada desde middleware Hitch | Caja: {U_NUMCAJA} | Local: {U_LOCAL}',
     *     'lines'      => [ ... ], // líneas procesadas desde $dataReceipt
     *     // + posibles campos adicionales UDF (user-defined fields) agregados por userDefinedFieldsReceipt()
     *   ]
     *
     * Ejemplo de línea procesada dentro de "lines":
     *   [
     *     "ItemCode"      => "0501010001639",
     *     "Quantity"      => 2,
     *     "AccountCode"   => "7021100030",
     *     "WarehouseCode" => "ANGOL",
     *     "CostingCode"   => "SANGOL",
     *     "CostingCode2"  => "AANGOL",
     *     "CostingCode3"  => "ONLINE"
     *   ]
     *
     * @note Los CostingCode pueden variar en nomenclatura y cantidad dependiendo 
     *       de la parametrización del cliente en SAP.
     */
    public function prepareItemDataReceipt(object $headerData, array $dataReceipt): array
    {

        // Valores forzados ambiente de pruebas, despues quitar
        // $dataReceipt = array_map(function ($line) {
        //     $line = (array) $line;
        //     $line['AccountCode'] = '7021100030'; // valor de prueba
        //     $line['WarehouseCode'] = 'ANGOL'; // valor de prueba
        //     $line['CostingCode'] = 'SANGOL'; // valor de prueba
        //     $line['CostingCode2'] = 'AANGOL'; // valor de prueba

        //     return $line;
        // }, $dataReceipt);

        $dataReceipt = array_map(function ($line) {

            $line['WarehouseCode'] = trim($line['WarehouseCode']);
            // $line['AccountCode']   = $this->modeTest ? '7021100030' : trim($line['AccountCode']);
            $line['CostingCode']   = $this->modeTest ? 'SANGOL' : trim($line['CostingCode']);  #CostingCode = SUCURSAL (CC1 EN POS)
            $line['CostingCode2']  = $this->modeTest ? 'AANGOL' : trim($line['CostingCode2']); #CostingCode2 = AREA (CC4 EN POS)
            $line['CostingCode3']  = $this->modeTest ? null : trim($line['CostingCode3']); #CostingCode3 = CANAL DE VENTA (CC3 EN POS)
            return $line;
        }, $dataReceipt);

        $today = date('Y-m-d');
        $local = trim($headerData->U_LOCAL);
        $fieldMapping = [
            'DocDate'    => !empty($headerData->DocDate) ? ordenar_fechaServidor($headerData->DocDate) : $today,
            'DocDueDate' => !empty($headerData->DocDueDate) ? ordenar_fechaServidor($headerData->DocDueDate) : $today,
            'TaxDate'    => !empty($headerData->DocDate) ? ordenar_fechaServidor($headerData->DocDate) : $today,
            'Reference2' => "AuditPOS",
            'Comments' => "Entrada generada desde middleware Hitch | Caja: $headerData->U_NUMCAJA | Local: $local",
            'lines' => $dataReceipt
        ];

        // Campos adicionales U_
        $arrUdf = $this->userDefinedFieldsReceipt($headerData);

        $data = array_merge($fieldMapping, $arrUdf);
        return $data;
    }

    public function userDefinedFieldsReceipt($data = [])
    {
        return [
            // 'U_INTEGRACION'  => "S",
        ];
    }


    private function validateStock(int $docNum, $sqlPosService, $retry = false)
    {
        $stockErrors = []; #ERRORES DE PRODUCTOS NO ENCONTRADOS EN LA BODEGA
        $dataReceipt = []; #LOS PRODUCTOS QUE DEBEN GENERAR UNA ENTRADA DE MERCADERIA
        $itemFilters = []; #ITEMCODE Y WHSCODE PARA BUSCARLOS EN HANA

        #SE OBTIENE DETALLE DE VENTAS DEL POS POR NÚMERO DE DOCUMENTO

        $tableDetail = $this->tableDetail;
        $getDataDetail = $sqlPosService->executeQuery('SELECT * FROM [dbo].[' . $tableDetail . '] WHERE ParentKey = ?', [$docNum]);
        if (!$getDataDetail['success']) {
            return [
                'success' => false,
                'error' => "",
                'connection' => false,
                'error_connection' =>  "Error al consultar stock masivo: {$getDataDetail['error']}"
            ];
        }

        # Agrupar cantidades por ItemCode + WarehouseCode
        $groupedDetails = collect($getDataDetail['data'])
            ->filter(fn($d) => $d->Quantity > 0) #descarta negativos
            ->groupBy(fn($d) => $d->ItemCode . '|' . ($this->modeTest ? 'ANGOL' : trim($d->WarehouseCode)))
            ->map(function ($group) {
                $first = $group->first();

                return (object) [
                    'ItemCode'       => $first->ItemCode,
                    'WarehouseCode'  => $this->modeTest ? 'ANGOL' : trim($first->WarehouseCode),
                    // 'AccountCode'    => $first->AccountCode,
                    'CostingCode'    => $first->CostingCode,
                    'CostingCode2'   => $first->CostingCode2,
                    'CostingCode3'   => $first->CostingCode3,
                    'Quantity'       => $group->sum(fn($d) => (float) $d->Quantity),
                    'Lines'          => $group, #las líneas originales
                ];
            });


        #SE OBTIENEN CODIGO DE PRODUCTO Y BODEGA PARA VALIDAR STOCK
        foreach ($groupedDetails as $detail) {
            $warehouse = $this->modeTest ? 'ANGOL' : $detail->WarehouseCode;
            $itemFilters[] = "('{$detail->ItemCode}', '{$warehouse}')";
        }

        $itemFilters = implode(", ", $itemFilters); #('CODIGO1', 'BODEGA1'), ('CODIGO2', 'BODEGA2')

        #INSTANCIAMOS SERVICIO PARA UTILIZAR LA API PARA QUERYS EN HANA
        $queryApiService = new QueryApiService();

        #EJECUTAMOS QUERY PARA OBTENER EL STOCK/PRECIO DE CADA PRODUCTO POR BODEGA SELECCIONADA
        $sqlStock = "
                SELECT 
                    T1.\"ItemCode\",
                    T1.\"InvntItem\", 
                    T1.\"AvgPrice\", 
                    T0.\"WhsCode\",
                    SUM(T0.\"OnHand\") AS \"ItemStock\"
                FROM OITW T0
                INNER JOIN OITM T1 ON T1.\"ItemCode\" = T0.\"ItemCode\"
                WHERE (T0.\"ItemCode\", T0.\"WhsCode\") IN ($itemFilters)
                GROUP BY T1.\"ItemCode\", T1.\"AvgPrice\", T0.\"WhsCode\", T1.\"InvntItem\"
            ";

        $queryStockResult = $queryApiService->executeQuery($sqlStock);
        if (!$queryStockResult['success']) {
            return $this->returnError("", false, "Error al consultar stock masivo: {$queryStockResult['error']}");
        }


        #GUARDAMOS TODOS LOS PRODUCTOS ENCONTRADOS
        $stockData = collect($queryStockResult['body']['data']);

        #SE RECORREN ITEMS
        foreach ($groupedDetails as $detail) {
            $stockLine = $stockData->first(
                fn($s) => $s['ItemCode'] === $detail->ItemCode && $s['WhsCode'] === $detail->WarehouseCode
            );

            if (!$stockLine) {
                $stockErrors[] = "No se encontró stock para {$detail->ItemCode} en bodega {$detail->WarehouseCode}";
                continue;
            }

            $detail->AvgPrice = $stockLine['AvgPrice'];
            $detail->Price = $stockLine['AvgPrice'];


            // Para pruebas ya que quedaron en $0
            $detail->Price = $this->modeTest || $detail->Price < 1 ? 1000 : $detail->Price;
            // $detail->Price = 1000;

            # VALIDAMOS SI EL ARTICULO ES INVENTARIABLE
            $isInventoryItem = in_array(strUpper($stockLine['InvntItem']), ['Y', 'TYES']);
            
            #SI QTY ES MENOR A 0 SE CONSIDERA DEVOLUCIÓN LA CUAL NO CUMPLE PARA RECEPCIONAR
            if ($detail->Quantity > 0) {

                #SI NO TENGO STOCK SUFICIENTE TENGO QUE SACAR DIFERENCIA Y DEJARLO PARA GENERAR ENTRADA DE MERCADERIA
                // if ($stockLine['ItemStock'] < $detail->Quantity  || (!$retry)) {

                if ($stockLine['ItemStock'] < $detail->Quantity && $isInventoryItem) {
                    $detail->ItemStock = $stockLine['ItemStock'];

                    #SI YO TENGO EN QTY 3 Y EN ITEMSTOK 1 Difference = 2
                    $detail->Difference = $detail->Quantity - $stockLine['ItemStock'];
                    // $detail->Difference = 1;

                    $dataReceipt[] =  collect($detail)->toArray();
                }
            }
        }

        #SE VALIDAR SI EXISTEN PRODUCTOS SIN BODEGA
        if (!empty($stockErrors)) {

            $tableHeader = $this->tableHeader;
            #EN CASO DE ERROR EN PROCESO SE GUARDA EN BASE DE DATOS DE POS
            $updateCab = $sqlPosService->executeStatement('UPDATE [dbo].[' . $tableHeader . '] SET Errors = :errors, DocEntry = :docentry WHERE DocNum = :docnum', [
                'errors' => json_encode($stockErrors),
                'docnum' => $docNum,
                'docentry' => null,
            ]);

            return [
                'success' => false,
                'error' => json_encode($stockErrors),
                'connection' =>  $updateCab['success'],
                'error_connection' =>  $updateCab['error']
            ];
        }

        #RETORNAMOS LOS PRODUCTOS A RECEPCIONAR Y LISTADO DE PRODUCTOS VENDIDOS
        return [
            'success' => true,
            'dataReceipt' =>  $dataReceipt,
            'getDataDetail' =>  $getDataDetail
        ];
    }

    private function returnError(string $error = '', bool $connection = true, string $errorConnection = NULL): array
    {
        if (!$connection) {
            return [
                'success' => false,
                'error' => [
                    'error_connection_pos' => $errorConnection,
                    'error' => json_encode($error)
                ],
                'logs' => [
                    'createBodyReceipt' => $this->createBodyReceipt,
                    'createBodyBill'    => $this->createBodyBill,
                    'responseReceipt'   => $this->responseReceipt,
                ]
            ];
        }

        return [
            'success' => false,
            'error' => $error,
            'logs' => [
                'createBodyReceipt' => $this->createBodyReceipt,
                'createBodyBill'    => $this->createBodyBill,
                'responseReceipt'   => $this->responseReceipt,
            ]
        ];
    }
}
