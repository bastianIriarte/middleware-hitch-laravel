<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\ReserveInvoiceStoreRequest;
use App\Services\PurchaseInvoicesService;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use App\Services\WmsApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Facturas de Reserva",
 *     description="Gestión de facturas de reserva en SAP"
 * )
 */
class PurchaseInvoicesController extends Controller
{
    protected $sapService;
    protected $purchaseInvoicesService;

    public function __construct(
        SapServiceLayerService $sapService,
        PurchaseInvoicesService $purchaseInvoicesService
    ) {
        $this->sapService = $sapService;
        $this->purchaseInvoicesService = $purchaseInvoicesService;
    }

    /**
     * @OA\Get(
     *     path="/facturas-reserva",
     *     summary="Obtener facturas de reserva con filtros y paginación",
     *     tags={"Facturas de Reserva"},
     *     @OA\Parameter(
     *         name="CardCode",
     *         in="query",
     *         description="Código del proveedor",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="DateFrom",
     *         in="query",
     *         description="Fecha desde (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="")
     *     ),
     *     @OA\Parameter(
     *         name="DateTo",
     *         in="query",
     *         description="Fecha hasta (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="")
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
     *         description="Facturas de reserva obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Facturas de reserva obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="DocEntry", type="integer", example=1001),
     *                     @OA\Property(property="DocNum", type="integer", example=40001),
     *                     @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                     @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26"),
     *                     @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="FolioPref", type="string", example="FR"),
     *                     @OA\Property(property="FolioNum", type="integer", example=12345),
     *                     @OA\Property(property="U_INTEGRACION", type="string", example="FMMS"),
     *                     @OA\Property(property="U_STATUS", type="string", example="Enviar / No enviar"),
     *                     @OA\Property(
     *                         property="lines",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="ItemCode", type="string", example="0501010001639"),
     *                             @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                             @OA\Property(property="Price", type="number", format="float", example=157.5),
     *                             @OA\Property(property="WhsCode", type="string", example="CD01"),
     *                             @OA\Property(property="TaxCode", type="string", example="IVA"),
     *                             @OA\Property(property="OcrCode", type="string", example="CC001"),
     *                             @OA\Property(property="OcrCode2", type="string", example="PRJ001"),
     *                             @OA\Property(property="OcrCode3", type="string", example="ACT001"),
     *                             @OA\Property(property="BaseType", type="integer", example=20),
     *                             @OA\Property(property="BaseEntry", type="integer", example=456),
     *                             @OA\Property(property="BaseLine", type="integer", example=0),
     *                             @OA\Property(property="U_SEI_CARPETA", type="string", example="CARP001")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=85),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-23T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No autenticado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener facturas de reserva"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión con SAP"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     )
     * )
     */

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];

            // --- Filtros dinámicos ---
            if ($request->filled('CardCode')) {
                $filters[] = "CardCode eq '{$request->query('CardCode')}'";
            }

            if ($request->filled('DateFrom')) {
                $filters[] = "DocDate ge '{$request->query('DateFrom')}'";
            }

            if ($request->filled('DateTo')) {
                $filters[] = "DocDate le '{$request->query('DateTo')}'";
            }

            $filterQuery = count($filters) > 0 ? implode(' and ', $filters) : null;

            // --- Paginación ---
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params = [
                '$select' => 'DocEntry,DocNum,CardCode,DocDate,DocDueDate,TaxDate,FolioPrefixString,FolioNumber,DocumentLines',
                '$orderby' => 'DocEntry desc',
                '$top' => $perPage,
                '$skip' => $skip,
            ];

            if (!empty($filterQuery)) {
                $params['$filter'] = $filterQuery;
            }

            // --- Consulta principal ---
            $response = $this->sapService->get('/PurchaseInvoices', $params);
            $items = $response['response']['value'] ?? [];

            // --- Mapeo de estructura ---
            $mappedItems = collect($items)->map(function ($item) {
                return [
                    'DocEntry'      => $item['DocEntry'] ?? null,
                    'DocNum'        => $item['DocNum'] ?? null,
                    'CardCode'      => $item['CardCode'] ?? null,
                    'DocDate'       => $item['DocDate'] ?? null,
                    'DocDueDate'    => $item['DocDueDate'] ?? null,
                    'TaxDate'       => $item['TaxDate'] ?? null,
                    'FolioPref'     => $item['FolioPrefixString'] ?? null,
                    'FolioNum'      => $item['FolioNumber'] ?? null,
                    'U_INTEGRACION' => $item['U_INTEGRACION'] ?? null,
                    'U_STATUS'      => $item['U_STATUS'] ?? null,
                    'lines'         => collect($item['DocumentLines'] ?? [])->map(function ($line) {
                        return [
                            'ItemCode'      => $line['ItemCode'] ?? null,
                            'Quantity'      => $line['Quantity'] ?? 0,
                            'Price'         => $line['Price'] ?? 0,
                            'WhsCode'       => $line['WarehouseCode'] ?? null,
                            'TaxCode'       => $line['TaxCode'] ?? null,
                            'OcrCode'       => $line['CostingCode'] ?? null,
                            'OcrCode2'      => $line['CostingCode2'] ?? null,
                            'OcrCode3'      => $line['CostingCode3'] ?? null,
                            'BaseType'      => $line['BaseType'] ?? null,
                            'BaseEntry'     => $line['BaseEntry'] ?? null,
                            'BaseLine'      => $line['BaseLine'] ?? 0,
                            'U_SEI_CARPETA' => $line['U_SEI_CARPETA'] ?? null,
                        ];
                    })->toArray(),
                ];
            })->toArray();

            // --- Obtener total seguro ---
            $totalItems = null;
            try {
                $countUrl = "/PurchaseInvoices/\$count";
                $countParams = [];

                if ($filterQuery) {
                    $countParams['$filter'] = $filterQuery;
                }

                $countResponse = $this->sapService->get($countUrl, $countParams);
                $totalItems = (int) ($countResponse['response'] ?? 0);
            } catch (\Throwable $t) {
                Log::warning("No se pudo obtener el total de facturas de reserva: " . $t->getMessage());
                $totalItems = count($items); // fallback si falla el conteo
            }

            // --- Respuesta final ---
            return ApiResponse::success([
                'data' => $mappedItems,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => $perPage > 0 ? ceil($totalItems / $perPage) : 1,
            ], 'Facturas de reserva obtenidas exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener facturas de reserva: ' . $formattedException->message);
            return ApiResponse::error(
                'Error al obtener facturas de reserva',
                [$formattedException->message],
                500
            );
        }
    }



    /**
     * @OA\Get(
     *     path="/facturas-reserva/{docEntry}",
     *     summary="Obtener factura de reserva específica",
     *     tags={"Facturas de Reserva"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento de factura de reserva",
     *         @OA\Schema(type="integer", example=1001)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Factura de reserva obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Factura de reserva obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=1001),
     *                 @OA\Property(property="DocNum", type="integer", example=40001),
     *                 @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                 @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26"),
     *                 @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="FolioPref", type="string", example="FR"),
     *                 @OA\Property(property="FolioNum", type="integer", example=12345),
     *                 @OA\Property(property="U_INTEGRACION", type="string", enum={"S", "N"}, example="S", description="Origen de la línea: S (Integración) o N (Manual)."),
     *                 @OA\Property(property="U_STATUS", type="string", example="Enviar / No enviar"),
     *                 @OA\Property(
     *                     property="lines",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="ItemCode", type="string", example="0501010001639"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                         @OA\Property(property="Price", type="number", format="float", example=157.5),
     *                         @OA\Property(property="WhsCode", type="string", example="CD01"),
     *                         @OA\Property(property="TaxCode", type="string", example="IVA"),
     *                         @OA\Property(property="OcrCode", type="string", example="CC001"),
     *                         @OA\Property(property="OcrCode2", type="string", example="PRJ001"),
     *                         @OA\Property(property="OcrCode3", type="string", example="ACT001"),
     *                         @OA\Property(property="BaseType", type="integer", example=20),
     *                         @OA\Property(property="BaseEntry", type="integer", example=456),
     *                         @OA\Property(property="BaseLine", type="integer", example=0),
     *                         @OA\Property(property="U_SEI_CARPETA", type="string", example="CARP001")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Factura de reserva no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Factura de reserva no encontrada"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Documento no existe"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener factura de reserva"),
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
                // '$select' => 'DocEntry,DocNum,CardCode,CardName,DocDate,DocDueDate,TaxDate,FolioPrefixString,FolioNumber,U_INTEGRACION,U_STATUS,DocumentLines'
                '$select' => 'DocEntry,DocNum,CardCode,CardName,DocDate,DocDueDate,TaxDate,FolioPrefixString,FolioNumber,DocumentLines'
            ];

            $response = $this->sapService->get("/PurchaseInvoices({$docEntry})", $params);
            $invoice = $response['response'] ?? [];

            if (empty($invoice)) {
                return ApiResponse::error('Factura de reserva no encontrada', ['Documento no existe'], 404);
            }

            // Mapear al formato esperado
            $mappedResponse = [
                'DocEntry'      => $invoice['DocEntry'] ?? null,
                'DocNum'        => $invoice['DocNum'] ?? null,
                'CardCode'      => $invoice['CardCode'] ?? null,
                'DocDate'       => $invoice['DocDate'] ?? null,
                'DocDueDate'    => $invoice['DocDueDate'] ?? null,
                'TaxDate'       => $invoice['TaxDate'] ?? null,
                'FolioPref'     => $invoice['FolioPrefixString'] ?? null,
                'FolioNum'      => $invoice['FolioNumber'] ?? null,
                'U_INTEGRACION' => $invoice['U_INTEGRACION'] ?? null,
                'U_STATUS'      => $invoice['U_STATUS'] ?? null,
                'lines'         => collect($invoice['DocumentLines'] ?? [])->map(function ($line) {
                    return [
                        'ItemCode'     => $line['ItemCode'] ?? null,
                        'Quantity'     => (float) ($line['Quantity'] ?? 0),
                        'Price'        => (float) ($line['Price'] ?? 0),
                        'WhsCode'      => $line['WhsCode'] ?? null,
                        'TaxCode'      => $line['TaxCode'] ?? null,
                        'OcrCode'      => $line['OcrCode'] ?? null,
                        'OcrCode2'     => $line['OcrCode2'] ?? null,
                        'OcrCode3'     => $line['OcrCode3'] ?? null,
                        'BaseType'     => $line['BaseType'] ?? null,
                        'BaseEntry'    => $line['BaseEntry'] ?? null,
                        'BaseLine'     => $line['BaseLine'] ?? null,
                        'U_SEI_CARPETA' => $line['U_SEI_CARPETA'] ?? null
                    ];
                })->toArray()
            ];

            return ApiResponse::success($mappedResponse, 'Factura de reserva obtenida exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener factura de reserva {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Factura de reserva no encontrada' : 'Error al obtener factura de reserva';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }


    /**
     * @OA\Post(
     *     path="/facturas-reserva/crear",
     *     summary="Crear factura de reserva",
     *     tags={"Facturas de Reserva"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"CardCode", "DocDate", "DocDueDate", "TaxDate", "U_INTEGRACION", "lines"},
     *             @OA\Property(property="CardCode", type="string", maxLength=15, example="PCN000124", description="Código del proveedor (máx. 15 caracteres)."),
     *             @OA\Property(property="DocDate", type="string", format="date", example="2025-07-30", description="Fecha del documento (YYYY-MM-DD)."),
     *             @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-30", description="Fecha de vencimiento (YYYY-MM-DD)."),
     *             @OA\Property(property="TaxDate", type="string", format="date", example="2025-07-30", description="Fecha fiscal (YYYY-MM-DD)."),
     *             @OA\Property(property="FolioPref", type="string", maxLength=4, example="FR", description="Prefijo del folio (máx. 4 caracteres)."),
     *             @OA\Property(property="FolioNum", type="integer", example=12345, description="Número de folio (entero positivo)."),
     *             @OA\Property(
     *                 property="DocCurrency",
     *                 type="string",
     *                 maxLength=3,
     *                 example="CLP",
     *                 description="Tipo Divisa (CLP, USD, máx. 3 caracteres)."
     *             ),
     *             @OA\Property(property="U_INTEGRACION", type="string", enum={"S", "N"}, example="S", description="Origen de la línea: S (Integración) o N (Manual)."),
     *             @OA\Property(property="U_STATUS", type="string", enum={"Enviar", "No enviar"}, example="Enviar", description="Estado de la factura."),
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
     *                 description="Detalle de líneas de la factura de reserva.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"ItemCode", "Quantity", "Price", "BaseType", "BaseEntry", "BaseLine"},
     *                     @OA\Property(property="ItemCode", type="string", maxLength=50, example="0501010001639", description="Código del artículo (máx. 50 caracteres)."),
     *                     @OA\Property(property="Quantity", type="number", format="float", minimum=0.000001, example=100, description="Cantidad (mayor que 0)."),
     *                     @OA\Property(property="Currency", type="string", maxLength=3, example="CLP", description="Tipo Divisa (máx. 3 caracteres)."),          
     *                     @OA\Property(property="Price", type="number", format="float", minimum=0, example=157.50, description="Precio unitario (no negativo)."),
     *                     @OA\Property(property="WhsCode", type="string", maxLength=8, example="01", description="Código de almacén (opcional, máx. 8 caracteres)."),
     *                     @OA\Property(property="TaxCode", type="string", maxLength=8, example="IVA", description="Código de impuesto (opcional, máx. 8 caracteres)."),
     *                     @OA\Property(property="OcrCode", type="string", maxLength=8, example="", description="Centro de costo (opcional, máx. 8 caracteres)."),
     *                     @OA\Property(property="OcrCode2", type="string", maxLength=8, example="", description="Proyecto (opcional, máx. 8 caracteres)."),
     *                     @OA\Property(property="OcrCode3", type="string", maxLength=8, example="", description="Actividad (opcional, máx. 8 caracteres)."),
     *                     @OA\Property(property="BaseType", type="integer", example=null, description="Tipo de documento base."),
     *                     @OA\Property(property="BaseEntry", type="integer", example=null, description="Entrada de documento base."),
     *                     @OA\Property(property="BaseLine", type="integer", example=null, description="Línea de documento base."),
     *                     @OA\Property(property="U_SEI_CARPETA", type="string", maxLength=20, example="CARP001", description="Carpeta SEI (máx. 20 caracteres).")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Factura de reserva creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Factura de reserva creada exitosamente"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=1001),
     *                 @OA\Property(property="DocNum", type="integer", example=50001)
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
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Proveedor no existe", "Folio duplicado"}),
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
     *             @OA\Property(property="message", type="string", example="Error al crear factura de reserva"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión con SAP"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     )
     * )
     */

    public function store(ReserveInvoiceStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'reserve_invoices',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear factura de reserva: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->purchaseInvoicesService->prepareReserveInvoiceData($request->validated());

            IntegrationLogger::update('reserve_invoices', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->sapService->post('/PurchaseInvoices', $data);

            IntegrationLogger::update('reserve_invoices', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Factura de reserva creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);

            $wmsService = new WmsApiService();
            $wmsResponse = $wmsService->makeRequest('POST', 'auth/oc_notify',  [
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'TipoDoc' => 'FR'
            ]);

            return ApiResponse::success([
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ], 'Factura de reserva creada exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear factura de reserva: ' . $formattedException->message);

            $errorDetails = SapErrorHandlerService::parseError($formattedException->message, $e->getCode());

            IntegrationLogger::update('reserve_invoices', $integrationLog['data']->id, [
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
}
