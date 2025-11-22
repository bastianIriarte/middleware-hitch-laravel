<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\JournalEntryStoreRequest;
use App\Services\JournalEntriesService;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Asientos Contables",
 *     description="Gestión de asientos contables en SAP"
 * )
 */
class JournalEntriesController extends Controller
{
    protected $sapService;
    protected $journalEntriesService;

    public function __construct(SapServiceLayerService $sapService, JournalEntriesService $journalEntriesService)
    {
        $this->sapService = $sapService;
        $this->journalEntriesService = $journalEntriesService;
    }

    /**
     * @OA\Get(
     *     path="/asientos",
     *     summary="Obtener asientos contables con filtros y paginación",
     *     tags={"Asientos Contables"},
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
     *         name="reference",
     *         in="query",
     *         description="Referencia del asiento",
     *         @OA\Schema(type="string", example="AST-001")
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
     *         description="Asientos contables obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asientos contables obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="JdtNum", type="integer", example=1001),
     *                     @OA\Property(property="RefDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="DueDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="Memo", type="string", example="Asiento de prueba"),
     *                     @OA\Property(property="Reference", type="string", example="AST-001"),
     *                     @OA\Property(property="Reference2", type="string", example="REF2-001"),
     *                     @OA\Property(property="CreationDate", type="string", format="date-time", example="2025-07-19T10:00:00")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=80),
     *             @OA\Property(property="total_pages", type="integer", example=4),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-23T10:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];

            // --- Filtros dinámicos ---
            if ($request->filled('dateFrom')) {
                $filters[] = "RefDate ge '{$request->query('dateFrom')}'";
            }

            if ($request->filled('dateTo')) {
                $filters[] = "RefDate le '{$request->query('dateTo')}'";
            }

            if ($request->filled('reference')) {
                $filters[] = "contains(Reference, '{$request->query('reference')}')";
            }

            $filterQuery = count($filters) > 0 ? implode(' and ', $filters) : null;

            // --- Paginación ---
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params = [
                '$orderby' => 'JdtNum desc',
                '$top' => $perPage,
                '$skip' => $skip
            ];

            if ($filterQuery) {
                $params['$filter'] = $filterQuery;
            }

            // --- Obtener asientos contables ---
            $response = $this->sapService->get('/JournalEntries', $params);
            $items = $response['response']['value'] ?? [];

            // --- Obtener total con /$count ---
            $totalItems = null;
            try {
                $countUrl = "/JournalEntries/\$count";
                $countParams = [];

                if ($filterQuery) {
                    $countParams['$filter'] = $filterQuery;
                }

                $countResponse = $this->sapService->get($countUrl, $countParams);
                $totalItems = (int) ($countResponse['response'] ?? 0);
            } catch (\Throwable $t) {
                Log::warning("No se pudo obtener el total de asientos: " . $t->getMessage());
                $totalItems = count($items); // fallback
            }

            // --- Respuesta final ---
            return ApiResponse::success([
                'data' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => $perPage > 0 ? ceil($totalItems / $perPage) : 1,
            ], 'Asientos contables obtenidos exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener asientos: ' . $formattedException->message);
            return ApiResponse::error(
                'Error al obtener asientos contables',
                [$formattedException->message],
                500
            );
        }
    }


    /**
     * @OA\Get(
     *     path="/asientos/{jdtNum}",
     *     summary="Obtener asiento contable específico",
     *     tags={"Asientos Contables"},
     *     @OA\Parameter(
     *         name="jdtNum",
     *         in="path",
     *         required=true,
     *         description="Número del asiento contable",
     *         @OA\Schema(type="integer", example=1001)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asiento contable obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asiento contable obtenido exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="JdtNum", type="integer", example=1001),
     *                 @OA\Property(property="RefDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="DueDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="Memo", type="string", example="Asiento de prueba"),
     *                 @OA\Property(property="Reference", type="string", example="AST-001"),
     *                 @OA\Property(property="Reference2", type="string", example="REF2-001"),
     *                 @OA\Property(
     *                     property="JournalEntryLines",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="Line_ID", type="integer", example=0),
     *                         @OA\Property(property="AccountCode", type="string", example="1110001"),
     *                         @OA\Property(property="ShortName", type="string", example="Caja"),
     *                         @OA\Property(property="Debit", type="number", format="float", example=1000.00),
     *                         @OA\Property(property="Credit", type="number", format="float", example=0.00),
     *                         @OA\Property(property="ContraAccount", type="string", example="4110001"),
     *                         @OA\Property(property="CostingCode", type="string", example="CC001"),
     *                         @OA\Property(property="CostingCode2", type="string", example="PRJ001"),
     *                         @OA\Property(property="CostingCode3", type="string", example="ACT001")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     * )
     */
    public function show(int $jdtNum): JsonResponse
    {
        try {
            $params = [
            ];

            $response = $this->sapService->get("/JournalEntries({$jdtNum})", $params);

            return ApiResponse::success($response['response'], 'Asiento contable obtenido exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener asiento {$jdtNum}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Asiento contable no encontrado' : 'Error al obtener asiento contable';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/asientos/crear",
     *     summary="Crear asiento contable",
     *     tags={"Asientos Contables"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"RefDate", "DueDate", "TaxDate", "memo", "lines"},
     *             @OA\Property(property="RefDate", type="string", format="date", example="2025-08-27", description="Fecha de contabilización"),
     *             @OA\Property(property="DueDate", type="string", format="date", example="2025-08-27", description="Fecha de vencimiento"),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-08-27", description="Fecha de documento"),
     *             @OA\Property(property="memo", type="string", example="Asiento de prueba", description="Comentario/Memo del asiento"),
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
     *                 description="Líneas del asiento contable (deben cuadrar débitos y créditos)",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"AccountCode"},
     *                     @OA\Property(property="AccountCode", type="string", example="3031200020", description="Código de cuenta contable"),
     *                     @OA\Property(property="Debit", type="number", format="float", example=10000, description="Importe débito en moneda local"),
     *                     @OA\Property(property="Credit", type="number", format="float", example=0, description="Importe crédito en moneda local"),
     *                     @OA\Property(property="FCDebit", type="number", format="float", example=null, description="Importe débito en moneda extranjera"),
     *                     @OA\Property(property="FCCredit", type="number", format="float", example=null, description="Importe crédito en moneda extranjera"),
     *                     @OA\Property(property="FCCurrency", type="string", example="", description="Moneda extranjera (ej: USD, EUR)"),
     *                     @OA\Property(property="DueDate", type="string", format="date", example="2025-08-27", description="Fecha de vencimiento de la línea"),
     *                     @OA\Property(property="LineMemo", type="string", example="TEST: Débito Caja CLP", description="Comentario de la línea"),
     *                     @OA\Property(property="ReferenceDate1", type="string", format="date", example="2025-08-27", description="Fecha de referencia de la línea"),
     *                     @OA\Property(property="CostingCode", type="string", example="", description="Centro de costo (Dimensión 1)"),
     *                     @OA\Property(property="CostingCode2", type="string", example="", description="Dimensión 2"),
     *                     @OA\Property(property="TaxDate", type="string", format="date", example="2025-08-27", description="Fecha del documento en la línea"),
     *                     @OA\Property(property="CashFlowLineItemID",type="integer",example=2,description="ID del ítem de flujo de caja (Posición del formulario principal)")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asiento contable creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Asiento contable creado exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="JdtNum", type="integer", example=1001),
     *                 @OA\Property(property="TransId", type="integer", example=123456)
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-27T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"El asiento no está balanceado", "Código de cuenta inválido"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-27T10:00:00-04:00")
     *         )
     *     )
     * )
     */
    public function store(JournalEntryStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'journal_entries',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear asiento: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            // pre_die($request->validated());
            $data = $this->journalEntriesService->prepareCreateData($request->validated());

            // pre_die($data);

            IntegrationLogger::update('journal_entries', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->journalEntriesService->sendData($data);

            IntegrationLogger::update('journal_entries', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Asiento contable creado exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'Number' => $response['response']['Number'] ?? null,
                'JdtNum' => $response['response']['JdtNum'] ?? null
            ], 'Asiento contable creado exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear asiento: ' . $formattedException->message);

            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('journal_entries', $integrationLog['data']->id, [
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
