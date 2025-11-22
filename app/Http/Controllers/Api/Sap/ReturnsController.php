<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\ReturnStoreRequest;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Devoluciones",
 *     description="Gestión de devoluciones de compras en SAP"
 * )
 */
class ReturnsController extends Controller
{
    protected $sapService;

    public function __construct(SapServiceLayerService $sapService)
    {
        $this->sapService = $sapService;
    }

   /**
     * @OA\Get(
     *     path="/devoluciones",
     *     summary="Obtener devoluciones de compras con filtros y paginación",
     *     tags={"Devoluciones"},
     *     @OA\Parameter(
     *         name="cardCode",
     *         in="query",
     *         description="Código del proveedor",
     *         required=false,
     *         @OA\Schema(type="string", example="PROV001")
     *     ),
     *     @OA\Parameter(
     *         name="dateFrom",
     *         in="query",
     *         description="Fecha desde (formato: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="dateTo",
     *         in="query",
     *         description="Fecha hasta (formato: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-07-31")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de devoluciones por página",
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
     *         description="Devoluciones obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Devoluciones obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="DocEntry", type="integer", example=1001),
     *                     @OA\Property(property="DocNum", type="integer", example=60001),
     *                     @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                     @OA\Property(property="CardName", type="string", example="Proveedor ABC"),
     *                     @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="DocTotal", type="number", format="float", example=5000.00)
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=50),
     *             @OA\Property(property="total_pages", type="integer", example=3),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-23T12:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-23T12:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener devoluciones"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión con SAP"}),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-23T12:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params = [
                '$select' => 'DocEntry,DocNum,CardCode,CardName,DocDate,DocTotal',
                '$orderby' => 'DocEntry desc',
                '$top' => $perPage,
                '$skip' => $skip
            ];

            $filters = [];

            if ($request->has('cardCode')) {
                $filters[] = "CardCode eq '{$request->cardCode}'";
            }

            if ($request->has('dateFrom')) {
                $filters[] = "DocDate ge '{$request->dateFrom}'";
            }

            if ($request->has('dateTo')) {
                $filters[] = "DocDate le '{$request->dateTo}'";
            }

            if (!empty($filters)) {
                $params['$filter'] = implode(' and ', $filters);
            }

            $response = $this->sapService->get('/PurchaseReturns', $params);
            $items = $response['response']['value'] ?? [];

            // Conteo total
            $countParams = [
                '$select' => 'DocEntry',
                '$inlinecount' => 'allpages'
            ];

            if (!empty($filters)) {
                $countParams['$filter'] = implode(' and ', $filters);
            }

            $countResponse = $this->sapService->get('/PurchaseReturns', $countParams);
            $totalItems = $countResponse['response']['odata.count'] ?? count($items);

            return ApiResponse::success([
                'items' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => ceil($totalItems / $perPage),
            ], 'Devoluciones obtenidas exitosamente');

        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener devoluciones: ' . $formattedException->message);

            return ApiResponse::error(
                'Error al obtener devoluciones',
                [$formattedException->message],
                500
            );
        }
    }


    /**
     * @OA\Get(
     *     path="/devoluciones/{docEntry}",
     *     summary="Obtener devolución específica",
     *     tags={"Devoluciones"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de devolución",
     *         @OA\Schema(type="integer", example=1001)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Devolución obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Devolución obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=1001),
     *                 @OA\Property(property="DocNum", type="integer", example=60001),
     *                 @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                 @OA\Property(property="CardName", type="string", example="Proveedor ABC"),
     *                 @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="DocTotal", type="number", format="float", example=5000.00),
     *                 @OA\Property(
     *                     property="DocumentLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="LineNum", type="integer", example=0),
     *                         @OA\Property(property="ItemCode", type="string", example="SKU001"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=20),
     *                         @OA\Property(property="Price", type="number", format="float", example=250.00),
     *                         @OA\Property(property="LineTotal", type="number", format="float", example=5000.00),
     *                         @OA\Property(property="WhsCode", type="string", example="CD01"),
     *                         @OA\Property(property="BaseType", type="integer", example=20),
     *                         @OA\Property(property="BaseEntry", type="integer", example=2001),
     *                         @OA\Property(property="BaseLine", type="integer", example=0)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Devolución no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Devolución no encontrada"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Documento no existe"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener devolución"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function show(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$expand' => 'DocumentLines($select=LineNum,ItemCode,Quantity,Price,LineTotal,WhsCode,BaseType,BaseEntry,BaseLine)'
            ];

            $response = $this->sapService->get("/PurchaseReturns({$docEntry})", $params);

            return ApiResponse::success($response['response'], 'Devolución obtenida exitosamente');

        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener devolución {$docEntry}: " . $formattedException->message);
            
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Devolución no encontrada' : 'Error al obtener devolución';
            
            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/devoluciones/crear",
     *     summary="Crear devolución de compra",
     *     tags={"Devoluciones"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"CardCode", "CardName", "DocDate", "DocDueDate", "TaxDate", "lines"},
     *             @OA\Property(property="CardCode", type="string", example="PROV001", description="Código del proveedor"),
     *             @OA\Property(property="CardName", type="string", example="Proveedor ABC", description="Nombre del proveedor"),
     *             @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19", description="Fecha del documento"),
     *             @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26", description="Fecha de vencimiento"),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-19", description="Fecha fiscal"),
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
     *                     required={"ItemCode", "Quantity", "UnitPrice", "WhsCode", "BaseType", "BaseEntry", "BaseLine"},
     *                     @OA\Property(property="ItemCode", type="string", example="SKU001", description="Código del artículo"),
     *                     @OA\Property(property="Quantity", type="number", format="float", example=20, description="Cantidad a devolver"),
     *                     @OA\Property(property="UnitPrice", type="number", format="float", example=250.00, description="Precio unitario"),
     *                     @OA\Property(property="WhsCode", type="string", example="CD01", description="Código de almacén"),
     *                     @OA\Property(property="TaxCode", type="string", example="IVA19", description="Código de impuesto"),
     *                     @OA\Property(property="OcrCode", type="string", example="CC001", description="Centro de costo"),
     *                     @OA\Property(property="OcrCode2", type="string", example="PRJ001", description="Proyecto"),
     *                     @OA\Property(property="OcrCode3", type="string", example="ACT001", description="Actividad"),
     *                     @OA\Property(property="BaseType", type="integer", example=20, description="Tipo de documento base (20=Entrada de mercadería)"),
     *                     @OA\Property(property="BaseEntry", type="integer", example=2001, description="Entrada de documento base"),
     *                     @OA\Property(property="BaseLine", type="integer", example=0, description="Línea de documento base")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Devolución creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Devolución creada exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=1001),
     *                 @OA\Property(property="DocNum", type="integer", example=60001)
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Cantidad excede lo recibido", "Artículo no válido para devolución"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear devolución"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión con SAP"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function store(ReturnStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'returns',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear devolución: {$integrationLog['message']}"
            ,[],500);
        }

        try {
            $data = $this->prepareReturnData($request->validated());

            IntegrationLogger::update('returns', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);
            
            $response = $this->sapService->post('/PurchaseReturns', $data);

            IntegrationLogger::update('returns', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Devolución creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ], 'Devolución creada exitosamente', 201);

        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear devolución: ' . $formattedException->message);
            
            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('returns', $integrationLog['data']->id, [
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
                    'suggestions' => $errorDetails['suggestions'],
                    'original_error' => config('app.debug') ? $formattedException->message : null
                ],
                $errorDetails['status_code']
            );
        }
    }

    private function prepareReturnData(array $validatedData): array
    {
        $data = [
            'CardCode' => $validatedData['CardCode'],
            'CardName' => $validatedData['CardName'],
            'DocDate' => $validatedData['DocDate'],
            'DocDueDate' => $validatedData['DocDueDate'],
            'TaxDate' => $validatedData['TaxDate']
        ];

        // Líneas del documento
        if (isset($validatedData['lines'])) {
            $data['DocumentLines'] = [];
            
            foreach ($validatedData['lines'] as $line) {
                $data['DocumentLines'][] = [
                    'ItemCode' => $line['ItemCode'],
                    'Quantity' => (float) $line['Quantity'],
                    'Price' => (float) $line['UnitPrice'],
                    'WarehouseCode' => $line['WhsCode'],
                    'TaxCode' => $line['TaxCode'] ?? null,
                    'CostingCode' => $line['OcrCode'] ?? null,
                    'CostingCode2' => $line['OcrCode2'] ?? null,
                    'CostingCode3' => $line['OcrCode3'] ?? null,
                    'BaseType' => $line['BaseType'], // 20 para entrada de mercadería
                    'BaseEntry' => $line['BaseEntry'],
                    'BaseLine' => $line['BaseLine']
                ];
            }
        }

        return $data;
    }
}