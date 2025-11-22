<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\DepositStoreRequest;
use App\Services\DepositService;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Depósitos",
 *     description="Gestión de depósitos bancarios en SAP"
 * )
 */
class DepositsController extends Controller
{
    protected $sapService;
    protected $depositService;

    public function __construct(SapServiceLayerService $sapService, DepositService $depositService)
    {
        $this->sapService = $sapService;
        $this->depositService = $depositService;
    }
    /**
     * @OA\Get(
     *     path="/depositos",
     *     summary="Obtener depósitos con filtros y paginación",
     *     tags={"Depósitos"},
     *     @OA\Parameter(name="dateFrom", in="query", description="Fecha desde", @OA\Schema(type="string", example="2025-07-01")),
     *     @OA\Parameter(name="dateTo", in="query", description="Fecha hasta", @OA\Schema(type="string", example="2025-07-31")),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de depósitos por página",
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
     *         description="Depósitos obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Depósitos obtenidos exitosamente"),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=83),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-18T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener depósitos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener depósitos"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-18T22:47:48-04:00")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];

            if ($request->has('dateFrom')) {
                $filters[] = "DepositDate ge '{$request->dateFrom}'";
            }

            if ($request->has('dateTo')) {
                $filters[] = "DepositDate le '{$request->dateTo}'";
            }

            // Paginación
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params['$top'] = $perPage;
            $params['$skip'] = $skip;

            // Obtener depósitos paginados
            $response = $this->sapService->get('/Deposits', $params);
            $items = $response['response']['value'] ?? [];

            // Obtener total sin paginación
            $countParams = $params;
            unset($countParams['$top'], $countParams['$skip'], $countParams['$select']);
            $countParams['$inlinecount'] = 'allpages';
            $countParams['$select'] = 'DepositNumber';

            $countResponse = $this->sapService->get('/Deposits', $countParams);
            $totalItems = $countResponse['response']['odata.count'] ?? count($items);

            return ApiResponse::success([
                'data' => $items,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => ceil($totalItems / $perPage),
            ], 'Depósitos obtenidos exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener depósitos: ' . $formattedException->message);
            return ApiResponse::error('Error al obtener depósitos', [$formattedException->message], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/depositos/{depositNumber}",
     *     summary="Obtener depósito específico",
     *     tags={"Depósitos"},
     *     @OA\Parameter(
     *         name="depositNumber",
     *         in="path",
     *         required=true,
     *         description="Número del depósito",
     *         @OA\Schema(type="integer", example=10001)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Depósito obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Depósito obtenido exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DepositNumber", type="integer", example=10001),
     *                 @OA\Property(property="BankName", type="string", example="Banco de Chile"),
     *                 @OA\Property(property="DepositTotal", type="number", example=125000.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Depósito no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Depósito no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener depósito",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener depósito"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */

    public function show(int $depositNumber): JsonResponse
    {
        try {
            $response = $this->sapService->get("/Deposits({$depositNumber})");

            return ApiResponse::success($response['response'], 'Depósito obtenido exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener depósito {$depositNumber}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Depósito no encontrado' : 'Error al obtener depósito';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }

    /**
     * @OA\Post(
     *     path="/depositos/crear",
     *     summary="Crear depósito",
     *     tags={"Depósitos"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para crear un depósito en SAP",
     *         @OA\JsonContent(
     *             required={"BankAcc", "DeposDate", "DpsBank", "DeposBrch", "DposAcct", "DpostorNam", "AlocAcct"},
     *             @OA\Property(property="DeposCurr", type="string", maxLength=3, example="CLP", description="Moneda del depósito (3 caracteres, opcional)"),
     *             @OA\Property(property="BankAcc", type="string", maxLength=20, example="001", description="Número de cuenta bancaria (máx. 20 caracteres)"),
     *             @OA\Property(property="DeposDate", type="string", format="date", example="2025-08-26", description="Fecha del depósito en formato YYYY-MM-DD"),
     *             @OA\Property(property="DpsBank", type="string", maxLength=50, example="Banco de Chile", description="Nombre del banco (máx. 50 caracteres)"),
     *             @OA\Property(property="DeposBrch", type="string", maxLength=50, example="Santiago", description="Sucursal del banco (máx. 50 caracteres)"),
     *             @OA\Property(property="DposAcct", type="string", maxLength=20, example="1011100090", description="Cuenta de depósito (máx. 20 caracteres)"),
     *             @OA\Property(property="DpostorNam", type="string", maxLength=100, example="Empresa ABC Ltda.", description="Nombre del depositante (máx. 100 caracteres)"),
     *             @OA\Property(property="AlocAcct", type="string", maxLength=20, example="1011100090", description="Cuenta contable asignada (máx. 20 caracteres)"),
     *             @OA\Property(property="DocTotalLC", type="number", format="float", example=150000, description="Monto en moneda local. Obligatorio si no se indica moneda extranjera"),
     *             @OA\Property(property="DocTotalFC", type="number", format="float", example=200.5, description="Monto en moneda extranjera. Obligatorio si se indica moneda"),
     *             @OA\Property(property="Memo", type="string", maxLength=254, example="Pago de facturas julio", description="Comentario opcional (máx. 254 caracteres)"),
     *             @OA\Property(
     *                 property="ORIGEN_PETICION",
     *                 type="string",
     *                 maxLength=15,
     *                 example="FMMS",
     *                 description="Origen de donde se realiza la petición FMMS, WMS, ETC."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Depósito creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Depósito creado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DepositNumber", type="integer", example=10002)
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T10:30:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={
     *                 "La cuenta del banco es obligatoria.",
     *                 "El monto en moneda local es obligatorio si no se indica moneda extranjera."
     *             }),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T10:30:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear depósito"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SAP Error: ODBC -2028"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-26T10:30:00-04:00")
     *         )
     *     )
     * )
     */
    public function store(DepositStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'deposits',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear depósito: {$integrationLog['message']}"
            ,[],500);
        }

        try {
            // pre_die($request->validated());
            $data = $this->depositService->prepareCreateData($request->validated());
            $data['DepositType'] = "C"; // Tipo efectivo
            // pre_die(json_encode($data));

            IntegrationLogger::update('deposits', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);
            
            $response = $this->depositService->sendData($data);

            IntegrationLogger::update('deposits', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'depósito creado exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            return ApiResponse::success([
                'DepositNumber' => $response['response']['DepositNumber'] ?? null
            ], 'Depósito creado exitosamente', 201);

        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear depósito: ' . $formattedException->message);
            
            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('deposits', $integrationLog['data']->id, [
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
