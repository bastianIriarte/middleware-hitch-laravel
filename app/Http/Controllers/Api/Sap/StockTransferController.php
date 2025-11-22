<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\StockTransferStoreRequest;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use App\Services\StockTransferService;
use App\Services\WmsApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Transferencia de Stock",
 *     description="Gestión de transferencia de stock en SAP"
 * )
 */

class StockTransferController extends Controller
{
    protected $sapService;
    protected $stockTransferService;

    public function __construct(
        SapServiceLayerService $sapService,
        StockTransferService $stockTransferService
    ) {
        $this->sapService = $sapService;
        $this->stockTransferService = $stockTransferService;
    }


    // ============================================
    // TRANSFERENCIAS DE STOCK (OWTR)
    // ============================================

    /**
     * @OA\Get(
     *     path="/inventario/transferencias",
     *     summary="Obtener transferencias de stock con filtros y paginación",
     *     tags={"Transferencia de Stock"},
     *     @OA\Parameter(
     *         name="dateFrom",
     *         in="query",
     *         description="Fecha desde",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="dateTo",
     *         in="query",
     *         description="Fecha hasta",
     *         @OA\Schema(type="string", format="date", example="2025-07-31")
     *     ),
     *     @OA\Parameter(
     *         name="fromWarehouse",
     *         in="query",
     *         description="Almacén de origen",
     *         @OA\Schema(type="string", example="CD01")
     *     ),
     *     @OA\Parameter(
     *         name="toWarehouse",
     *         in="query",
     *         description="Almacén de destino",
     *         @OA\Schema(type="string", example="BO02")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de resultados por página",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Parameter(
     *         name="current_page",
     *         in="query",
     *         description="Número de página actual",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transferencias de stock obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transferencias de stock obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="DocEntry", type="integer", example=1),
     *                     @OA\Property(property="DocNum", type="integer", example=1001),
     *                     @OA\Property(property="PostingDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="CardCode", type="string", example="CL00001234"),
     *                     @OA\Property(property="CardName", type="string", example="Local La Florida"),
     *                     @OA\Property(property="FromWarehouse", type="string", example="CD01"),
     *                     @OA\Property(property="ToWarehouse", type="string", example="BO02"),
     *                     @OA\Property(property="U_Conductor", type="string", example="12.345.678-9 Juan Soto"),
     *                     @OA\Property(property="U_Pat", type="string", example="XY1234"),
     *                     @OA\Property(property="DocTotal", type="number", format="float", example=8250.00)
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=88),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-23T12:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function getStockTransfers(Request $request): JsonResponse
    {
        try {
            $filters = [];

            if ($request->filled('dateFrom')) {
                $filters[] = "DocDate ge '{$request->dateFrom}'";
            }

            if ($request->filled('dateTo')) {
                $filters[] = "DocDate le '{$request->dateTo}'";
            }

            if ($request->has('fromWarehouse')) {
                $filters[] = "FromWarehouse eq '{$request->fromWarehouse}'";
            }

            if ($request->has('toWarehouse')) {
                $filters[] = "ToWarehouse eq '{$request->toWarehouse}'";
            }

            $filterQuery = count($filters) > 0 ? implode(' and ', $filters) : null;

            // Paginación
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params = [
                '$top' => $perPage,
                '$skip' => $skip
            ];

            if ($filterQuery) {
                $params['$filter'] = $filterQuery;
            }

            // Obtener datos
            $response = $this->sapService->get('/StockTransfers', $params);
            $items = $response['response']['value'] ?? [];

            // Obtener total de manera segura
            $totalItems = null;
            try {
                $countUrl = "/StockTransfers/\$count";
                $countParams = $filterQuery ? ['$filter' => $filterQuery] : [];
                $countResponse = $this->sapService->get($countUrl, $countParams);
                $totalItems = (int) ($countResponse['response'] ?? 0);
            } catch (\Throwable $t) {
                // Evita romper si SAP no soporta /$count o timeout
                Log::warning("No se pudo obtener el total de transferencias: " . $t->getMessage());
                $totalItems = count($items);
            }

            return ApiResponse::success([
                'data' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => $perPage > 0 ? ceil($totalItems / $perPage) : 1,
            ], 'Transferencias de stock obtenidas exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());
            Log::error('Error al obtener transferencias: ' . $formattedException->message);
            return ApiResponse::error('Error al obtener transferencias', [$formattedException->message], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/inventario/transferencias/{docEntry}",
     *     summary="Obtener transferencia específica",
     *     tags={"Transferencia de Stock"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de transferencia",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transferencia obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transferencia obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001),
     *                 @OA\Property(property="CardCode", type="string", example="CL00001234"),
     *                 @OA\Property(property="FromWarehouse", type="string", example="CD01"),
     *                 @OA\Property(property="ToWarehouse", type="string", example="BO02"),
     *                 @OA\Property(
     *                     property="StockTransferLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="ItemDescription", type="string", example="Producto ABC"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                         @OA\Property(property="Price", type="number", format="float", example=25.50),
     *                         @OA\Property(property="WarehouseCode", type="string", example="CD01"),
     *                         @OA\Property(property="BaseType", type="integer", example=67),
     *                         @OA\Property(property="BaseEntry", type="integer", example=456),
     *                         @OA\Property(property="BaseLine", type="integer", example=0)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * )
     */
    public function showStockTransfer(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$expand' => 'StockTransferLines($select=LineNum,ItemCode,ItemDescription,Quantity,Price,WarehouseCode,BaseType,BaseEntry,BaseLine)'
            ];

            $response = $this->sapService->get("/StockTransfers({$docEntry})");

            return ApiResponse::success($response['response'], 'Transferencia obtenida exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener transferencia {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Transferencia no encontrada' : 'Error al obtener transferencia';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/inventario/transferencias/crear",
     *     summary="Crear transferencia de stock (Guía de traslado) desde FMMS en SAP",
     *     tags={"Transferencia de Stock"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "CardCode", "CardName", "DocDate",
     *                 "TaxDate", "Filler", "ToWhsCode", "U_Integracion", "Series", "lines"
     *             },
     *             @OA\Property(property="CardCode", type="string", example="CCL79899520", description="Código del socio de negocios"),
     *             @OA\Property(property="DocDate", type="string", format="date", example="2025-08-26", description="Fecha de contabilización"),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-08-26", description="Fecha fiscal del documento"),
     *             @OA\Property(property="Filler", type="string", example="CMATRIZ", description="Almacén de origen"),
     *             @OA\Property(property="ToWhsCode", type="string", example="ANGOL", description="Almacén de destino"),
     *             @OA\Property(property="U_Integracion", type="string", enum={"S","N"}, example="S", description="Indicador de integración con SAP"),
     *             @OA\Property(property="Series", type="integer", enum={83,27}, example=83, description="Serie del documento: 83 = GD_Elect, 27 = Primario"),
     *
     *             @OA\Property(property="U_BFE_TipoDoctoRef", type="string", maxLength=50, example="", description="Tipo de documento de referencia"),
     *             @OA\Property(property="U_BFE_FechaRef", type="string", format="date-time", example="2025-08-26T12:00:00", description="Fecha de referencia"),
     *             @OA\Property(property="U_BFE_IndTraslado", type="string", maxLength=50, example="1", description="Indicador de traslado"),
     *             @OA\Property(property="U_BFE_RutChofer", type="string", maxLength=10, example="1-9", description="RUT del chofer"),
     *             @OA\Property(property="U_BFE_NombreChofer", type="string", maxLength=30, example="Juan Pérez", description="Nombre del chofer"),
     *             @OA\Property(property="U_BFE_RutTrasporte", type="string", maxLength=10, example="1-9", description="RUT de la empresa de transporte"),
     *             @OA\Property(property="U_BFE_Patente", type="string", maxLength=8, example="ABCD12", description="Patente del vehículo"),
     *             @OA\Property(
     *                 property="ORIGEN_PETICION",
     *                 type="string",
     *                 maxLength=15,
     *                 example="FMMS",
     *                 description="Origen de donde se realiza la petición FMMS, WMS, ETC."
     *             ),
     *             @OA\Property(
     *                 property="lines",
     *                 type="array",
     *                 minItems=1,
     *                 maxItems=24,
     *                 @OA\Items(
     *                     type="object",
     *                     required={"ItemCode", "Quantity"},
     *                     @OA\Property(property="ItemCode", type="string", example="0202010000002", description="Código del artículo"),
     *                     @OA\Property(property="Quantity", type="number", format="float", example=3, description="Cantidad a transferir"),
     *                     @OA\Property(property="Price", type="number", format="float", example=1000, description="Precio de producto"),
     *                     @OA\Property(property="WhsCode", type="string", example="", description="Almacén destino de la línea (opcional)")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transferencia de stock creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transferencia de stock creada correctamente"),
     *             @OA\Property(property="data", type="object", example={"DocNum": 54321}),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T12:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={} ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T12:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permiso para acceder a este recurso"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={} ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T12:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recurso no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Recurso no encontrado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={} ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T12:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={} ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T12:00:00-04:00")
     *         )
     *     )
     * )
     */

    public function createStockTransfer(StockTransferStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'stock_transfer',
            [
                'service_name' => 'Crear Transferencia de Stock',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al enviar transferencia de stock: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->stockTransferService->prepareCreateData($request->validated());

            // pre_die(json_encode($data));

            IntegrationLogger::update('stock_transfer', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->stockTransferService->sendData($data);

            IntegrationLogger::update('stock_transfer', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Transferencia de stock creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            $responseSap = [
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null,
                'FolioPrefixString' => $response['response']['FolioPrefixString'] ?? null,
                'FolioNumber' => $response['response']['FolioNumber'] ?? null
            ];

            // $wmsService = new WmsApiService();
            // $wmsResponse = $wmsService->makeRequest('POST', 'auth/oc_notify',  $responseSap);
            return ApiResponse::success($responseSap, 'Transferencia de stock creada exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear transferencia de stock: ' . $formattedException->message);

            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('stock_transfer', $integrationLog['data']->id, [
                'code' => $errorDetails['status_code'] ?? 400,
                'request_body' => $formattedException->request ?? json_encode($formattedException->request, JSON_UNESCAPED_UNICODE),
                'message' => $errorDetails['user_message'],
                'response' => $errorDetails,
                'status_integration_id' => 4,
            ]);

            return ApiResponse::error(
                $errorDetails['user_message'],
                [
                    'error_code' => $errorDetails['error_code'],
                    'original_error' => config('app.debug') ? $formattedException->message : null
                ],
                $errorDetails['status_code']
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/inventario/transferencias/desde-solicitud",
     *     summary="Crear transferencia desde solicitud de traslado",
     *     tags={"Transferencia de Stock"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"base_doc_entry", "from_warehouse", "to_warehouse", "lines"},
     *             @OA\Property(property="base_doc_entry", type="integer", example=123, description="ID de la solicitud de traslado base"),
     *             @OA\Property(property="from_warehouse", type="string", example="CD01", description="Almacén de origen"),
     *             @OA\Property(property="to_warehouse", type="string", example="BO02", description="Almacén de destino"),
     *             @OA\Property(
     *                 property="ORIGEN_PETICION",
     *                 type="string",
     *                 maxLength=15,
     *                 example="FMMS",
     *                 description="Origen de donde se realiza la petición FMMS, WMS, ETC."
     *             ),
     *             @OA\Property(
     *                 property="lines",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     required={"base_line"},
     *                     @OA\Property(property="base_line", type="integer", example=0, description="Número de línea de la solicitud base"),
     *                     @OA\Property(property="quantity", type="number", format="float", example=50, description="Cantidad a transferir (opcional para transferencia parcial)")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transferencia creada desde solicitud exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transferencia creada desde solicitud exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=456),
     *                 @OA\Property(property="DocNum", type="integer", example=2001)
     *             )
     *         )
     *     ),
     * )
     */
    public function createTransferFromRequest(Request $request): JsonResponse
    {
        $request->validate([
            'base_doc_entry' => 'required|integer',
            'from_warehouse' => 'required|string|max:8',
            'to_warehouse' => 'required|string|max:8',
            'lines' => 'required|array',
            'lines.*.base_line' => 'required|integer',
            'lines.*.quantity' => 'nullable|numeric'
        ]);

        $integrationLog = IntegrationLogger::create(
            'stock_transfer_requests',
            [
                'service_name' => 'Crear transferencia desde solicitud',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear transferencia desde solicitud: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = [
                'FromWarehouse' => $request->from_warehouse,
                'ToWarehouse' => $request->to_warehouse,
                'StockTransferLines' => []
            ];

            foreach ($request->lines as $line) {
                $transferLine = [
                    'BaseEntry' => $request->base_doc_entry,
                    'BaseType' => 1250000001, // Código para solicitud de traslado
                    'BaseLine' => $line['base_line']
                ];

                // Solo agregar quantity si es diferente (transferencia parcial)
                if (isset($line['quantity'])) {
                    $transferLine['Quantity'] = (float) $line['quantity'];
                }

                $data['StockTransferLines'][] = $transferLine;
            }

            IntegrationLogger::update('stock_transfer_requests', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->sapService->post('/StockTransfers', $data);

            IntegrationLogger::update('stock_transfer_requests', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Transferencia creada desde solicitud exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ], 'Transferencia creada desde solicitud exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear transferencia desde solicitud: ' . $formattedException->message);

            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('stock_transfer_requests', $integrationLog['data']->id, [
                'code' => $errorDetails['status_code'] ?? 400,
                'request_body' => $formattedException->request ?? json_encode($formattedException->request, JSON_UNESCAPED_UNICODE),
                'message' => $errorDetails['user_message'],
                'response' => $errorDetails,
                'status_integration_id' => 4,
            ]);

            return ApiResponse::error(
                $errorDetails['user_message'],
                [
                    'error_code' => $errorDetails['error_code'],
                    'original_error' => config('app.debug') ? $formattedException->message : null
                ],
                $errorDetails['status_code']
            );
        }
    }
}
