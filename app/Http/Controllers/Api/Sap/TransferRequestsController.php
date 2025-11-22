<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\StockTransferRequestStoreRequest;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use App\Services\StockTransferRequestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Solicitudes de Traslado",
 *     description="Gestión de solicitudes de traslados en SAP"
 * )
 */

class TransferRequestsController extends Controller
{
    protected $sapService;
    protected $stockTransferRequestService;

    public function __construct(
        SapServiceLayerService $sapService,
        StockTransferRequestService $stockTransferRequestService
    ) {
        $this->sapService = $sapService;
        $this->stockTransferRequestService = $stockTransferRequestService;
    }

    // ============================================
    // SOLICITUDES DE TRASLADO (OWTQ)
    // ============================================


    /**
     * @OA\Get(
     *     path="/inventario/solicitudes-traslado",
     *     summary="Obtener solicitudes de traslado con filtros y paginación",
     *     tags={"Solicitudes de Traslado"},
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
     *         name="status",
     *         in="query",
     *         description="Estado del documento (O = Abierto, C = Cerrado)",
     *         @OA\Schema(type="string", enum={"O", "C"}, example="O")
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
     *         description="Solicitudes de traslado obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Solicitudes de traslado obtenidas exitosamente"),
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
     *                     @OA\Property(property="DocStatus", type="string", example="O"),
     *                     @OA\Property(property="U_Integracion", type="string", example="FMMS"),
     *                     @OA\Property(property="U_Status", type="string", example="Pendiente")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=54),
     *             @OA\Property(property="total_pages", type="integer", example=3),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-23T12:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function getTransferRequests(Request $request): JsonResponse
    {
        try {
            $filters = [];

            if ($request->filled('dateFrom')) {
                $filters[] = "DocDate ge '{$request->dateFrom}'";
            }

            if ($request->filled('dateTo')) {
                $filters[] = "DocDate le '{$request->dateTo}'";
            }

            if ($request->filled('status')) {
                $filters[] = "DocumentStatus eq '" . ($request->status == 'O' ? 'bost_Open' : 'bost_Close') . "'";
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

            $response = $this->sapService->get('/InventoryTransferRequests', $params);
            $items = $response['response']['value'] ?? [];

            // Total de ítems
            $countParams = [
                '$select' => 'DocEntry',
                '$inlinecount' => 'allpages'
            ];

            if ($filterQuery) {
                $countParams['$filter'] = $filterQuery;
            }

            $countResponse = $this->sapService->get('/InventoryTransferRequests', $countParams);
            $totalItems = $countResponse['response']['odata.count'] ?? count($items);

            return ApiResponse::success([
                'data' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => ceil($totalItems / $perPage),
            ], 'Solicitudes de traslado obtenidas exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener solicitudes de traslado: ' . $formattedException->message);
            return ApiResponse::error('Error al obtener solicitudes de traslado', [$formattedException->message], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/inventario/solicitudes-traslado/{docEntry}",
     *     summary="Obtener solicitud de traslado específica",
     *     tags={"Solicitudes de Traslado"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de solicitud",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de traslado obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Solicitud de traslado obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001),
     *                 @OA\Property(property="CardCode", type="string", example="CL00001234"),
     *                 @OA\Property(property="FromWarehouse", type="string", example="CD01"),
     *                 @OA\Property(property="ToWarehouse", type="string", example="BO02"),
     *                 @OA\Property(
     *                     property="StockTransferRequestLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="ItemDescription", type="string", example="Producto ABC"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                         @OA\Property(property="WarehouseCode", type="string", example="CD01")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     * )
     */

    public function showTransferRequest(int $docEntry): JsonResponse
    {
        try {
            $params = [
            ];

            $response = $this->sapService->get("/InventoryTransferRequests({$docEntry})", $params);

            return ApiResponse::success($response['response'], 'Solicitud de traslado obtenida exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener solicitud {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Solicitud no encontrada' : 'Error al obtener solicitud';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/inventario/solicitudes-traslado/crear",
     *     summary="Crear solicitud de traslado de inventario en SAP",
     *     tags={"Solicitudes de Traslado"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "CardCode", "CardName", "DocDate",
     *                 "TaxDate", "Filler", "ToWhsCode", "U_Integracion", "lines"
     *             },
     *             @OA\Property(property="CardCode", type="string", example="CCL79899520", description="Código del socio de negocios"),
     *             @OA\Property(property="DocDate", type="string", format="date", example="2025-08-26", description="Fecha de contabilización"),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-08-26", description="Fecha fiscal del documento"),
     *             @OA\Property(property="Filler", type="string", example="CMATRIZ", description="Almacén de origen"),
     *             @OA\Property(property="ToWhsCode", type="string", example="ANGOL", description="Almacén de destino"),
     *             @OA\Property(property="U_Integracion", type="string", enum={"S","N"}, example="S", description="Indicador de integración con SAP"),
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
     *                     @OA\Property(property="Quantity", type="number", format="float", example=3, description="Cantidad solicitada"),
     *                     @OA\Property(property="Price", type="number", format="float", example=1000, description="Precio de producto"),
     *                     @OA\Property(property="WhsCode", type="string", example="", description="Almacén destino de la línea (opcional)")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solicitud de traslado creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Solicitud de traslado creada correctamente"),
     *             @OA\Property(property="data", type="object", example={"DocNum": 98765}),
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
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión con SAP"} ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T12:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function createTransferRequest(StockTransferRequestStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'stock_transfer',
            [
                'service_name' => 'Crear Solicitud de Traslado',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al enviar solicitud de traslado: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->stockTransferRequestService->prepareCreateData($request->validated());

            // pre_die(json_encode($data));

            IntegrationLogger::update('stock_transfer', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->stockTransferRequestService->sendData($data);

            IntegrationLogger::update('stock_transfer', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Solicitud de traslado creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ], 'Solicitud de traslado creada exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear solicitud de traslado: ' . $formattedException->message);

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
}
