<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\GoodsIssueStoreRequest;
use App\Http\Requests\Sap\GoodsReceiptStoreRequest;
use App\Services\GoodsIssuesService;
use App\Services\GoodsReceiptService;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Movimientos de Inventario",
 *     description="Gestión de movimientos de inventario en SAP"
 * )
 */

class InventoryMovementsController extends Controller
{
    protected $sapService;
    protected $goodsReceiptService;
    protected $goodsIssuesService;

    public function __construct(
        SapServiceLayerService $sapService,
        GoodsReceiptService $goodsReceiptService,
        GoodsIssuesService $goodsIssuesService
    ) {
        $this->sapService = $sapService;
        $this->goodsReceiptService = $goodsReceiptService;
        $this->goodsIssuesService = $goodsIssuesService;
    }

    // ============================================
    // ENTRADAS DE INVENTARIO (OIGN)
    // ============================================

    /**
     * @OA\Get(
     *     path="/inventario/entradas",
     *     summary="Obtener entradas de inventario con filtros y paginación",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="dateFrom",
     *         in="query",
     *         description="Fecha desde (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="dateTo",
     *         in="query",
     *         description="Fecha hasta (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-31")
     *     ),
     *     @OA\Parameter(
     *         name="warehouse",
     *         in="query",
     *         description="Código de almacén para filtrar líneas",
     *         @OA\Schema(type="string", example="CD01")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de entradas por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, example=20)
     *     ),
     *     @OA\Parameter(
     *         name="current_page",
     *         in="query",
     *         description="Número de página actual",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entradas de inventario obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Entradas de inventario obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="DocEntry", type="integer", example=1),
     *                     @OA\Property(property="DocNum", type="integer", example=1001),
     *                     @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="Reference2", type="string", example="REF-1234"),
     *                     @OA\Property(property="DocTotal", type="number", format="float", example=12500.50),
     *                     @OA\Property(property="Comments", type="string", example="Entrada de prueba")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=83),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-18T22:47:48-04:00")
     *         )
     *     )
     * )
     */
    public function getInventoryEntries(Request $request): JsonResponse
    {
        try {
            $queryFilters = [];

            if ($request->has('dateFrom')) {
                $queryFilters[] = "DocDate ge datetime'{$request->query('dateFrom')}T00:00:00'";
            }

            if ($request->has('dateTo')) {
                $queryFilters[] = "DocDate le datetime'{$request->query('dateTo')}T23:59:59'";
            }

            $filter = count($queryFilters) > 0 ? implode(' and ', $queryFilters) : null;

            // Paginación
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            // Parámetros para obtener registros paginados
            $params = [
                '$select' => 'DocEntry,DocNum,DocDate,Reference2,DocTotal,Comments',
                '$orderby' => 'DocEntry desc',
                '$top' => $perPage,
                '$skip' => $skip
            ];

            if ($filter) {
                $params['$filter'] = $filter;
            }

            $response = $this->sapService->get('/InventoryGenEntries', $params);
            $entries = $response['response']['value'] ?? [];

            // Filtro adicional por warehouse (filtrado manual)
            if ($request->has('warehouse')) {
                $warehouse = $request->query('warehouse');
                $filteredEntries = [];

                foreach ($entries as $entry) {
                    $lines = $this->sapService->get("/InventoryGenEntries({$entry['DocEntry']})/InventoryGenEntryLines", [
                        '$filter' => "WarehouseCode eq '{$warehouse}'",
                        '$top' => 1
                    ]);

                    if (!empty($lines['response']['value'])) {
                        $filteredEntries[] = $entry;
                    }
                }

                $entries = $filteredEntries;
            }

            // Contar total de registros
            $countParams = [
                '$select' => 'DocEntry',
                '$inlinecount' => 'allpages'
            ];
            if ($filter) {
                $countParams['$filter'] = $filter;
            }
            $countResponse = $this->sapService->get('/InventoryGenEntries', $countParams);
            $totalItems = $countResponse['response']['odata.count'] ?? count($entries);

            return ApiResponse::success([
                'data' => $entries,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => ceil($totalItems / $perPage),
            ], 'Entradas de inventario obtenidas exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener entradas de inventario: ' . $formattedException->message);

            return ApiResponse::error('Error al obtener entradas de inventario', [$formattedException->message], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/inventario/entradas/{docEntry}",
     *     summary="Obtener entrada de inventario específica",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de entrada",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entrada de inventario obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Entrada de inventario obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001),
     *                 @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="Reference2", type="string", example="REF-1234"),
     *                 @OA\Property(property="DocTotal", type="number", format="float", example=12500.50),
     *                 @OA\Property(
     *                     property="InventoryGenEntryLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="ItemDescription", type="string", example="Producto ABC"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                         @OA\Property(property="Price", type="number", format="float", example=125.50),
     *                         @OA\Property(property="WarehouseCode", type="string", example="CD01")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     * )
     */
    public function showInventoryEntry(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$expand' => 'InventoryGenEntryLines($select=LineNum,ItemCode,ItemDescription,Quantity,Price,WarehouseCode)'
            ];

            $response = $this->sapService->get("/InventoryGenEntries({$docEntry})", $params);

            return ApiResponse::success($response['response'], 'Entrada de inventario obtenida exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener entrada {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Entrada no encontrada' : 'Error al obtener entrada';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }


    // ============================================
    // SALIDAS DE INVENTARIO (OIGE)
    // ============================================


    /**
     * @OA\Get(
     *     path="/inventario/salidas",
     *     summary="Obtener salidas de inventario con filtros y paginación",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="dateFrom",
     *         in="query",
     *         description="Fecha desde (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="dateTo",
     *         in="query",
     *         description="Fecha hasta (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-31")
     *     ),
     *     @OA\Parameter(
     *         name="warehouse",
     *         in="query",
     *         description="Código de almacén",
     *         @OA\Schema(type="string", example="CD01")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de salidas por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, example=20)
     *     ),
     *     @OA\Parameter(
     *         name="current_page",
     *         in="query",
     *         description="Número de página actual",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salidas de inventario obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Salidas de inventario obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="DocEntry", type="integer", example=1),
     *                     @OA\Property(property="DocNum", type="integer", example=1001),
     *                     @OA\Property(property="PostingDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="Reference2", type="string", example="REF-OUT-001"),
     *                     @OA\Property(property="DocTotal", type="number", format="float", example=8500.00),
     *                     @OA\Property(property="U_SEI_Ref", type="string", example="SEI-001"),
     *                     @OA\Property(property="U_Origen", type="string", example="FMMS"),
     *                     @OA\Property(property="U_Traslado", type="string", example="Enviar")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=83),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function getInventoryExits(Request $request): JsonResponse
    {
        try {
            $queryFilters = [];

            if ($request->has('dateFrom')) {
                $queryFilters[] = "PostingDate ge datetime'{$request->query('dateFrom')}T00:00:00'";
            }

            if ($request->has('dateTo')) {
                $queryFilters[] = "PostingDate le datetime'{$request->query('dateTo')}T23:59:59'";
            }

            $filter = count($queryFilters) > 0 ? implode(' and ', $queryFilters) : null;

            // Paginación
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params = [
                '$select' => 'DocEntry,DocNum,PostingDate,Reference2,DocTotal,U_SEI_Ref,U_Origen,U_Traslado',
                '$orderby' => 'DocEntry desc',
                '$top' => $perPage,
                '$skip' => $skip
            ];

            if ($filter) {
                $params['$filter'] = $filter;
            }

            $response = $this->sapService->get('/InventoryGenExits', $params);
            $items = $response['response']['value'] ?? [];

            // Contar total
            $countParams = [
                '$select' => 'DocEntry',
                '$inlinecount' => 'allpages'
            ];
            if ($filter) {
                $countParams['$filter'] = $filter;
            }

            $countResponse = $this->sapService->get('/InventoryGenExits', $countParams);
            $totalItems = $countResponse['response']['odata.count'] ?? count($items);

            return ApiResponse::success([
                'data' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => ceil($totalItems / $perPage),
            ], 'Salidas de inventario obtenidas exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener salidas de inventario: ' . $formattedException->message);
            return ApiResponse::error('Error al obtener salidas de inventario', [$formattedException->message], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/inventario/salidas/{docEntry}",
     *     summary="Obtener salida de inventario específica",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de salida",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Salida de inventario obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Salida de inventario obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001),
     *                 @OA\Property(property="PostingDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="Reference2", type="string", example="REF-OUT-001"),
     *                 @OA\Property(property="U_SEI_Ref", type="string", example="SEI-001"),
     *                 @OA\Property(
     *                     property="DocumentLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="ItemDescription", type="string", example="Producto ABC"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=50),
     *                         @OA\Property(property="WarehouseCode", type="string", example="CD01")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     * )
     */
    public function showInventoryExit(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$expand' => 'DocumentLines($select=LineNum,ItemCode,ItemDescription,Quantity,WarehouseCode)'
            ];

            $response = $this->sapService->get("/InventoryGenExits({$docEntry})", $params);

            return ApiResponse::success($response['response'], 'Salida de inventario obtenida exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener salida {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Salida no encontrada' : 'Error al obtener salida';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/inventario/salidas/crear",
     *     summary="Crear salida de inventario (Goods Issue)",
     *     tags={"Movimientos de Inventario"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"DocDate", "DocDueDate", "TaxDate", "lines"},
     *             @OA\Property(property="DocDate", type="string", format="date", example="2025-08-26", description="Fecha de contabilización del documento"),
     *             @OA\Property(property="Reference2", type="string", example="AuditPOS", description="Referencia 2"),
     *             @OA\Property(property="Comments", type="string", example="Test salida de mercaderia..", description="Comentarios del documento"),
     *             @OA\Property(property="DocDueDate", type="string", format="date", example="2025-08-26", description="Fecha de vencimiento del documento"),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-08-26", description="Fecha fiscal del documento"),
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
     *                     required={"ItemCode", "Quantity", "Price", "WhsCode"},
     *                     @OA\Property(property="ItemCode", type="string", example="2541268900001", description="Código del artículo"),
     *                     @OA\Property(property="Quantity", type="number", format="float", example=2, description="Cantidad del artículo"),
     *                     @OA\Property(property="Price", type="number", format="float", example=2500, description="Precio unitario del artículo"),
     *                     @OA\Property(property="WhsCode", type="string", example="ANGOL", description="Código del almacén"),
     *                     @OA\Property(property="AccountCode", type="string", example="7021100030", description="Código de cuenta contable"),
     *                     @OA\Property(property="CostingCode", type="string", example="SANGOL", description="Código de imputación 1"),
     *                     @OA\Property(property="CostingCode2", type="string", example="AANGOL", description="Código de imputación 2")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Salida de inventario creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Salida de inventario creada exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001)
     *             )
     *         )
     *     )
     * )
     */
    public function createInventoryExit(GoodsIssueStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'goods_issues',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear salida de inventario: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->goodsIssuesService->prepareCreateData($request->validated());

            IntegrationLogger::update('goods_issues', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->goodsIssuesService->sendData($data);

            IntegrationLogger::update('goods_issues', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Salida de inventario creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ], 'Salida de inventario creada exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear salida de inventario: ' . $formattedException->message);

            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('goods_issues', $integrationLog['data']->id, [
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

    // ============================================
    // ENTRADAS DE MERCADERÍA (OPDN)
    // ============================================

    /**
     * @OA\Get(
     *     path="/inventario/entradas-mercaderia",
     *     summary="Obtener entradas de mercadería (Purchase Delivery Notes)",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="dateFrom",
     *         in="query",
     *         description="Fecha desde (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="dateTo",
     *         in="query",
     *         description="Fecha hasta (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-31")
     *     ),
     *     @OA\Parameter(
     *         name="baseEntry",
     *         in="query",
     *         description="Documento base de referencia",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Parameter(
     *         name="baseType",
     *         in="query",
     *         description="Tipo de documento base",
     *         @OA\Schema(type="integer", example=22)
     *     ),
     *     @OA\Parameter(
     *         name="top",
     *         in="query",
     *         description="Número máximo de registros a retornar",
     *         @OA\Schema(type="integer", example=100)
     *     ),
     *     @OA\Parameter(
     *         name="skip",
     *         in="query",
     *         description="Número de registros a omitir",
     *         @OA\Schema(type="integer", example=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entradas de mercadería obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Entradas de mercadería obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="DocEntry", type="integer", example=1),
     *                     @OA\Property(property="DocNum", type="integer", example=1001),
     *                     @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                     @OA\Property(property="CardName", type="string", example="Proveedor ABC"),
     *                     @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="DocTotal", type="number", format="float", example=15000.00),
     *                     @OA\Property(property="BaseType", type="integer", example=22),
     *                     @OA\Property(property="BaseEntry", type="integer", example=456)
     *                 )
     *             )
     *         )
     *     ),
     * )
     */

    public function getGoodsReceipts(Request $request): JsonResponse
    {
        try {
            $params = [
                '$select' => 'DocEntry,DocNum,CardCode,CardName,DocDate,DocTotal,BaseType,BaseEntry',
                '$orderby' => 'DocEntry desc'
            ];

            $this->applyCommonFilters($params, $request);

            // Filtro por documento base si se especifica
            if ($request->has('baseEntry') && $request->has('baseType')) {
                $filters = $params['$filter'] ?? '';
                $baseFilter = "BaseEntry eq {$request->baseEntry} and BaseType eq {$request->baseType}";
                $params['$filter'] = empty($filters) ? $baseFilter : $filters . ' and ' . $baseFilter;
            }

            $response = $this->sapService->get('/PurchaseDeliveryNotes', $params);

            return ApiResponse::success(
                $response['response']['value'] ?? $response,
                'Entradas de mercadería obtenidas exitosamente'
            );
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener entradas de mercadería: ' . $formattedException->message);
            return ApiResponse::error('Error al obtener entradas de mercadería', [$formattedException->message], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/inventario/entradas-mercaderia/{docEntry}",
     *     summary="Obtener entrada de mercadería específica",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de entrada",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Entrada de mercadería obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Entrada de mercadería obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001),
     *                 @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                 @OA\Property(property="CardName", type="string", example="Proveedor ABC"),
     *                 @OA\Property(
     *                     property="DocumentLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="ItemDescription", type="string", example="Producto ABC"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                         @OA\Property(property="Price", type="number", format="float", example=25.50),
     *                         @OA\Property(property="WarehouseCode", type="string", example="CD01"),
     *                         @OA\Property(property="BaseType", type="integer", example=22),
     *                         @OA\Property(property="BaseEntry", type="integer", example=456),
     *                         @OA\Property(property="BaseLine", type="integer", example=0)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * )
     */
    public function showGoodsReceipt(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$expand' => 'DocumentLines($select=LineNum,ItemCode,ItemDescription,Quantity,Price,WarehouseCode,BaseType,BaseEntry,BaseLine)'
            ];

            $response = $this->sapService->get("/PurchaseDeliveryNotes({$docEntry})", $params);

            return ApiResponse::success($response['response'], 'Entrada de mercadería obtenida exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener entrada de mercadería {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Entrada de mercadería no encontrada' : 'Error al obtener entrada de mercadería';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/inventario/entradas-mercaderia/crear",
     *     summary="Crear entrada de mercadería (Purchase Delivery Note)",
     *     tags={"Movimientos de Inventario"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"DocDate", "DocDueDate", "TaxDate", "lines"},
     *             @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19", description="Fecha de contabilización del documento"),
     *             @OA\Property(property="Reference2", type="string", example="AuditPOS", description="Referencia2"),
     *             @OA\Property(property="Comments", type="string", example="Entrada de mercaderia comentarios..", description="Referencia2"),
     *             @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26", description="Fecha de vencimiento del documento"),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-19", description="Fecha fiscal del documento"),
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
     *                     required={"ItemCode", "Quantity", "Price", "WarehouseCode"},
     *                     @OA\Property(property="ItemCode", type="string", example="SKU001234", description="Código del artículo"),
     *                     @OA\Property(property="Quantity", type="number", format="float", example=100, description="Cantidad del artículo"),
     *                     @OA\Property(property="Price", type="number", format="float", example=25.50, description="Precio unitario del artículo"),
     *                     @OA\Property(property="WarehouseCode", type="string", example="CD01", description="Código del almacén"),
     *                     @OA\Property(property="AccountCode", type="string", example="ACT01", description="Código contable"),
     *                     @OA\Property(property="CostingCode", type="string", example="DPTO01", description="Código de imputación 1"),
     *                     @OA\Property(property="CostingCode2", type="string", example="PROY01", description="Código de imputación 2"),
     *                     @OA\Property(property="CostingCode3", type="string", example="PROY01", description="Código de imputación 3")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Entrada de mercadería creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Entrada de mercadería creada exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001)
     *             )
     *         )
     *     )
     * )
     */
    public function createGoodsReceipt(GoodsReceiptStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'goods_receipts',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear entrada de mercadería: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->goodsReceiptService->prepareCreateData($request->validated());

            IntegrationLogger::update('goods_receipts', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->goodsReceiptService->sendData($data);

            IntegrationLogger::update('goods_receipts', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Entrada de mercadería creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ], 'Entrada de mercadería creada exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear entrada de mercadería: ' . $formattedException->message);

            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('goods_receipts', $integrationLog['data']->id, [
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


    // ============================================
    // CONTEOS DE INVENTARIO (OINC)
    // ============================================


    /**
     * @OA\Get(
     *     path="/inventario/conteos",
     *     summary="Obtener conteos de inventario con filtros y paginación",
     *     tags={"Movimientos de Inventario"},
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
     *         name="warehouse",
     *         in="query",
     *         description="Código de almacén",
     *         @OA\Schema(type="string", example="CD01")
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
     *         description="Conteos de inventario obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conteos de inventario obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="DocEntry", type="integer", example=1),
     *                     @OA\Property(property="DocNum", type="integer", example=1001),
     *                     @OA\Property(property="PostingDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="CountDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="Reference", type="string", example="CONT-001"),
     *                     @OA\Property(property="Remarks", type="string", example="Conteo mensual"),
     *                     @OA\Property(property="CountType", type="string", example="ctSingleCounter")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=80),
     *             @OA\Property(property="total_pages", type="integer", example=4),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-23T12:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function getInventoryCountings(Request $request): JsonResponse
    {
        try {
            $filters = [];

            if ($request->has('dateFrom')) {
                $filters[] = "PostingDate ge datetime'{$request->query('dateFrom')}T00:00:00'";
            }

            if ($request->has('dateTo')) {
                $filters[] = "PostingDate le datetime'{$request->query('dateTo')}T23:59:59'";
            }

            if ($request->has('warehouse')) {
                $filters[] = "Warehouse eq '{$request->warehouse}'";
            }

            $filterQuery = count($filters) > 0 ? implode(' and ', $filters) : null;

            // Paginación
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params = [
                '$select' => 'DocEntry,DocNum,PostingDate,CountDate,Reference,Remarks,CountType',
                '$orderby' => 'DocEntry desc',
                '$top' => $perPage,
                '$skip' => $skip
            ];

            if ($filterQuery) {
                $params['$filter'] = $filterQuery;
            }

            $response = $this->sapService->get('/InventoryCountings', $params);
            $items = $response['response']['value'] ?? [];

            // Obtener total de registros
            $countParams = [
                '$select' => 'DocEntry',
                '$inlinecount' => 'allpages'
            ];

            if ($filterQuery) {
                $countParams['$filter'] = $filterQuery;
            }

            $countResponse = $this->sapService->get('/InventoryCountings', $countParams);
            $totalItems = $countResponse['response']['odata.count'] ?? count($items);

            return ApiResponse::success([
                'data' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => ceil($totalItems / $perPage),
            ], 'Conteos de inventario obtenidos exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener conteos de inventario: ' . $formattedException->message);
            return ApiResponse::error('Error al obtener conteos de inventario', [$formattedException->message], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/inventario/conteos/{docEntry}",
     *     summary="Obtener conteo específico",
     *     tags={"Movimientos de Inventario"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de conteo",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conteo obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conteo obtenido exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=123),
     *                 @OA\Property(property="DocNum", type="integer", example=1001),
     *                 @OA\Property(property="PostingDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="CountDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="Reference", type="string", example="CONT-001"),
     *                 @OA\Property(property="Remarks", type="string", example="Conteo mensual"),
     *                 @OA\Property(
     *                     property="InventoryCountingLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="ItemDescription", type="string", example="Producto ABC"),
     *                         @OA\Property(property="CountedQuantity", type="number", format="float", example=95),
     *                         @OA\Property(property="WarehouseCode", type="string", example="CD01")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * )
     */

    public function showInventoryCounting(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$expand' => 'InventoryCountingLines($select=LineNum,ItemCode,ItemDescription,CountedQuantity,WarehouseCode)'
            ];

            $response = $this->sapService->get("/InventoryCountings({$docEntry})", $params);

            return ApiResponse::success($response['response'], 'Conteo obtenido exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener conteo {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Conteo no encontrado' : 'Error al obtener conteo';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================

    private function applyCommonFilters(array &$params, Request $request): void
    {
        $filters = [];

        if ($request->has('dateFrom')) {
            $filters[] = "DocDate ge '{$request->dateFrom}'";
        }

        if ($request->has('dateTo')) {
            $filters[] = "DocDate le '{$request->dateTo}'";
        }

        if ($request->has('warehouse')) {
            $filters[] = "WarehouseCode eq '{$request->warehouse}'";
        }

        if (!empty($filters)) {
            $params['$filter'] = implode(' and ', $filters);
        }

        if ($request->has('top')) {
            $params['$top'] = $request->top;
        }

        if ($request->has('skip')) {
            $params['$skip'] = $request->skip;
        }
    }
}
