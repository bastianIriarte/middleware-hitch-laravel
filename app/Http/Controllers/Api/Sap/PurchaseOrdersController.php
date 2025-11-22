<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\PurchaseOrderStoreRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Services\PurchaseOrdersService;
use App\Services\SapServiceLayerService;
use App\Services\SapErrorHandlerService;
use App\Services\WmsApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Órdenes de Compra",
 *     description="Gestión de órdenes de compra en SAP"
 * )
 */
class PurchaseOrdersController extends Controller
{
    protected $sapService;
    protected $purchaseOrdersService;

    public function __construct(
        SapServiceLayerService $sapService,
        PurchaseOrdersService $purchaseOrdersService
    ) {
        $this->sapService = $sapService;
        $this->purchaseOrdersService = $purchaseOrdersService;
    }

    /**
     * @OA\Get(
     *     path="/ordenes-compra",
     *     summary="Obtener órdenes de compra con filtros y paginación",
     *     tags={"Órdenes de Compra"},
     *     @OA\Parameter(
     *         name="CardCode",
     *         in="query",
     *         description="Código del proveedor",
     *         @OA\Schema(type="string", example="PROV001")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Estado de la orden (O=Abierta, C=Cerrada)",
     *         @OA\Schema(type="string", enum={"O", "C"}, example="O")
     *     ),
     *     @OA\Parameter(
     *         name="DateFrom",
     *         in="query",
     *         description="Fecha desde (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-01")
     *     ),
     *     @OA\Parameter(
     *         name="DateTo",
     *         in="query",
     *         description="Fecha hasta (formato: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date", example="2025-07-31")
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
     *         description="Órdenes de compra obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Órdenes de compra obtenidas exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="DocEntry", type="integer", example=1001),
     *                     @OA\Property(property="DocNum", type="integer", example=40001),
     *                     @OA\Property(property="CardCode", type="string", example="PROV001"),
     *                     @OA\Property(property="CardName", type="string", example="Proveedor ABC"),
     *                     @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                     @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26"),
     *                     @OA\Property(property="NumAtCard", type="string", example="OC-2025-001"),
     *                     @OA\Property(property="U_ENCARGADO_COMPRA", type="string", example="Juan Pérez"),
     *                     @OA\Property(property="U_INVOICE", type="string", example="INV-001"),
     *                     @OA\Property(property="U_BL", type="string", example="BL-001"),
     *                     @OA\Property(property="U_ETD", type="string", example="2025-07-20"),
     *                     @OA\Property(property="U_ETA", type="string", example="2025-08-15"),
     *                     @OA\Property(property="U_PEMBARQUE", type="string", example="Shanghai"),
     *                     @OA\Property(property="U_PDESTINO", type="string", example="Valparaíso"),
     *                     @OA\Property(property="U_PCONSOLID", type="string", example="Hong Kong"),
     *                     @OA\Property(property="U_NSALIDA", type="string", example="MSC001"),
     *                     @OA\Property(property="U_NLLEGADA", type="string", example="MSC002"),
     *                     @OA\Property(property="U_FORWARDER", type="string", example="DHL Supply Chain"),
     *                     @OA\Property(property="U_AGENCIA", type="string", example="Agencia Marítima ABC"),
     *                     @OA\Property(property="U_CONTENEDOR", type="string", maxLength=50, example="CONT001"),
     *                     @OA\Property(property="U_SELLO", type="string", maxLength=50, example="SEAL001"),
     *                     @OA\Property(property="U_INTEGRACION", type="string", example="S"),
     *                     @OA\Property(
     *                         property="lines",
     *                         type="array",
     *                         description="Líneas de detalle de la orden de compra",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="ItemCode", type="string", example="0501010001639"),
     *                             @OA\Property(property="Quantity", type="number", example=100),
     *                             @OA\Property(property="Price", type="number", example=250.00),
     *                             @OA\Property(property="WhsCode", type="string", example="01"),
     *                             @OA\Property(property="OcrCode", type="string", example=""),
     *                             @OA\Property(property="OcrCode2", type="string", example=""),
     *                             @OA\Property(property="OcrCode3", type="string", example=""),
     *                             @OA\Property(property="TaxCode", type="string", example="IVA"),
     *                             @OA\Property(property="U_COMPO_FINAL", type="string", example="CF001"),
     *                             @OA\Property(property="U_SEI_Aprobador", type="string", example="Maria García"),
     *                             @OA\Property(property="U_Integracion", type="string", example="FMMS"),
     *                             @OA\Property(property="U_Status", type="string", example="Enviar")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_items", type="integer", example=85),
     *             @OA\Property(property="total_pages", type="integer", example=5),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener órdenes de compra"),
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
                $filters[] = "CardCode eq '{$request->CardCode}'";
            }

            if ($request->filled('status')) {
                $status = $request->status === 'O' ? 'bost_Open' : 'bost_Close';
                $filters[] = "DocumentStatus eq '{$status}'";
            }

            if ($request->filled('DateFrom')) {
                $filters[] = "DocDate ge '{$request->DateFrom}'";
            }

            if ($request->filled('DateTo')) {
                $filters[] = "DocDate le '{$request->DateTo}'";
            }

            $filterQuery = count($filters) > 0 ? implode(' and ', $filters) : null;

            // --- Paginación ---
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            // --- Campos y orden ---
            $params = [
                '$select' => 'DocEntry,DocNum,CardCode,CardName,DocDate,DocDueDate,NumAtCard,U_INVOICE,U_BL,U_ETD,U_ETA,U_PEMBARQUE,U_PDESTINO,U_PCONSOLID,U_NSALIDA,U_NLLEGADA,U_FORWARDER,U_AGENCIA,U_CONTENEDOR,U_INTEGRACION,DocumentLines',
                '$orderby' => 'DocEntry desc',
                '$top' => $perPage,
                '$skip' => $skip,
            ];

            if ($filterQuery) {
                $params['$filter'] = $filterQuery;
            }

            // --- Consulta principal ---
            $response = $this->sapService->get('/PurchaseOrders', $params);
            $items = $response['response']['value'] ?? [];

            // --- Mapeo / Transformación ---
            $mappedItems = PurchaseOrderResource::collection($items);

            // --- Conteo eficiente y seguro ---
            $totalItems = null;
            try {
                $countUrl = "/PurchaseOrders/\$count";
                $countParams = [];

                if ($filterQuery) {
                    $countParams['$filter'] = $filterQuery;
                }

                $countResponse = $this->sapService->get($countUrl, $countParams);
                $totalItems = (int) ($countResponse['response'] ?? 0);
            } catch (\Throwable $t) {
                Log::warning("No se pudo obtener el total de órdenes de compra: " . $t->getMessage());
                $totalItems = count($mappedItems); // fallback si falla el conteo
            }

            // --- Respuesta final ---
            return ApiResponse::success([
                'data' => $mappedItems,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => $perPage > 0 ? ceil($totalItems / $perPage) : 1,
            ], 'Órdenes de compra obtenidas exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener órdenes de compra: ' . $formattedException->message);

            return ApiResponse::error(
                'Error al obtener órdenes de compra',
                [$formattedException->message],
                500
            );
        }
    }



    /**
     * @OA\Get(
     *     path="/ordenes-compra/{docEntry}",
     *     summary="Obtener una orden de compra específica",
     *     tags={"Órdenes de Compra"},
     *     @OA\Parameter(
     *         name="docEntry",
     *         in="path",
     *         required=true,
     *         description="ID del documento (DocEntry) de la orden de compra",
     *         @OA\Schema(type="integer", example=1001)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orden de compra obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orden de compra obtenida exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="DocEntry", type="integer", example=1001),
     *                 @OA\Property(property="DocNum", type="integer", example=40001),
     *                 @OA\Property(property="CardCode", type="string", maxLength=15, example="PROV001"),
     *                 @OA\Property(property="CardName", type="string", example="Proveedor ABC"),
     *                 @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19"),
     *                 @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26"),
     *                 @OA\Property(property="NumAtCard", type="string", maxLength=200, example="OC-2025-001"),
     *                 @OA\Property(property="U_ENCARGADO_COMPRA", type="string", maxLength=50, example="Juan Pérez"),
     *                 @OA\Property(property="U_INVOICE", type="string", maxLength=50, example="INV-001"),
     *                 @OA\Property(property="U_BL", type="string", maxLength=50, example="BL-001"),
     *                 @OA\Property(property="U_ETD", type="string", format="date", example="2025-07-20"),
     *                 @OA\Property(property="U_ETA", type="string", format="date", example="2025-08-15"),
     *                 @OA\Property(property="U_PEMBARQUE", type="string", maxLength=50, example="Shanghai"),
     *                 @OA\Property(property="U_PDESTINO", type="string", maxLength=50, example="Valparaíso"),
     *                 @OA\Property(property="U_PCONSOLID", type="string", maxLength=50, example="Hong Kong"),
     *                 @OA\Property(property="U_NSALIDA", type="string", maxLength=50, example="MSC001"),
     *                 @OA\Property(property="U_NLLEGADA", type="string", maxLength=50, example="MSC002"),
     *                 @OA\Property(property="U_FORWARDER", type="string", maxLength=50, example="DHL Supply Chain"),
     *                 @OA\Property(property="U_AGENCIA", type="string", maxLength=50, example="Agencia Marítima ABC"),
     *                 @OA\Property(
     *                     property="CONTENEDOR",
     *                     type="array",
     *                     description="Líneas del UDO CONTENEDOR asociadas a esta orden",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="U_CONTENEDOR", type="string", example="12312312"),
     *                         @OA\Property(property="U_SELLO", type="string", example="333334"),
     *                         @OA\Property(property="U_DOC_ENTRY_PO", type="integer", example=168),
     *                         @OA\Property(property="U_DOC_NUM_PO", type="integer", example=88)
     *                     )
     *                 ),
     *                 @OA\Property(property="U_INTEGRACION", type="string", enum={"S", "N"}, example="S"),
     *                 @OA\Property(
     *                     property="lines",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="ItemCode", type="string", maxLength=50, example="0501010001639"),
     *                         @OA\Property(property="U_COMPO_FINAL", type="string", maxLength=50, example="CF001"),
     *                         @OA\Property(property="Quantity", type="number", format="float", example=100),
     *                         @OA\Property(property="Price", type="number", format="float", example=250),
     *                         @OA\Property(property="TaxCode", type="string", maxLength=8, example="IVA"),
     *                         @OA\Property(property="WhsCode", type="string", maxLength=8, example="01"),
     *                         @OA\Property(property="OcrCode", type="string", maxLength=8, example=""),
     *                         @OA\Property(property="OcrCode2", type="string", maxLength=8, example=""),
     *                         @OA\Property(property="OcrCode3", type="string", maxLength=8, example=""),
     *                         @OA\Property(property="U_SEI_Aprobador", type="string", maxLength=20, example="Maria García"),
     *                         @OA\Property(property="U_Integracion", type="string", enum={"S", "N"}, example="S"),
     *                         @OA\Property(property="U_Status", type="string", enum={"Enviar", "No enviar"}, example="Enviar")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Orden de compra no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Orden de compra no encontrada"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Documento no existe"}),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-19T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener orden de compra"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión"}),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-19T10:00:00-04:00")
     *         )
     *     )
     * )
     */

    public function show(int $docEntry): JsonResponse
    {
        try {
            $params = [
                '$select' => 'DocEntry,DocNum,CardCode,CardName,DocDate,DocDueDate,NumAtCard,U_INVOICE,U_BL,U_ETD,U_ETA,U_PEMBARQUE,U_PDESTINO,U_PCONSOLID,U_NSALIDA,U_NLLEGADA,U_FORWARDER,U_AGENCIA,U_CONTENEDOR,U_INTEGRACION,DocumentLines',
            ];

            $response = $this->sapService->get("/PurchaseOrders({$docEntry})", $params);
            $data = $response['response'] ?? [];

            $resource = (new PurchaseOrderResource($data))->withContainer();
            return ApiResponse::success(
                $resource->toArray(request()),
                'Orden de compra obtenida exitosamente'
            );
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener orden de compra {$docEntry}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Orden de compra no encontrada' : 'Error al obtener orden de compra';

            return ApiResponse::error($message, [$formattedException->message], $statusCode);
        }
    }


    /**
     * @OA\Post(
     *     path="/ordenes-compra/crear",
     *     summary="Crear orden de compra y opcionalmente generar factura de reserva",
     *     description="Crea una orden de compra en SAP con sus líneas de detalle. Si `CreateInvoice = tYES`, se genera una factura de reserva asociada automáticamente.",
     *     tags={"Órdenes de Compra"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"CardCode", "DocDate", "DocDueDate", "NumAtCard", "U_INTEGRACION", "lines"},
     *             @OA\Property(
     *                 property="CardCode",
     *                 type="string",
     *                 maxLength=15,
     *                 example="PCN000124",
     *                 description="Código único del proveedor en SAP (máx. 15 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="DocDate",
     *                 type="string",
     *                 format="date",
     *                 example="2025-07-30",
     *                 description="Fecha de contabilización de la orden de compra (formato YYYY-MM-DD)."
     *             ),
     *             @OA\Property(
     *                 property="DocDueDate",
     *                 type="string",
     *                 format="date",
     *                 example="2025-07-30",
     *                 description="Fecha de entrega de la orden de compra (formato YYYY-MM-DD)."
     *             ),
     *             @OA\Property(
     *                 property="NumAtCard",
     *                 type="string",
     *                 maxLength=200,
     *                 example="OC-2025-001",
     *                 description="Referencia externa (folio FMMS, máx. 200 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="DocCurrency",
     *                 type="string",
     *                 maxLength=3,
     *                 example="CLP",
     *                 description="Tipo Divisa (CLP, USD, máx. 200 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_ENCARGADO_COMPRA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Juan Pérez",
     *                 description="Nombre del encargado de compras (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_INVOICE",
     *                 type="string",
     *                 maxLength=50,
     *                 example="INV-001",
     *                 description="Número de invoice (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_BL",
     *                 type="string",
     *                 maxLength=50,
     *                 example="BL-001",
     *                 description="Número de Bill of Lading (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_ETD",
     *                 type="string",
     *                 format="date",
     *                 example="2025-07-20",
     *                 description="Fecha estimada de salida (ETD, opcional, formato YYYY-MM-DD)."
     *             ),
     *             @OA\Property(
     *                 property="U_ETA",
     *                 type="string",
     *                 format="date",
     *                 example="2025-08-15",
     *                 description="Fecha estimada de arribo (ETA, opcional, formato YYYY-MM-DD)."
     *             ),
     *             @OA\Property(
     *                 property="U_PEMBARQUE",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Shanghai",
     *                 description="Puerto de embarque (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_PDESTINO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Valparaíso",
     *                 description="Puerto de destino (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_PCONSOLID",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Hong Kong",
     *                 description="Puerto de consolidación (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_NSALIDA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="MSC001",
     *                 description="Nave de salida (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_NLLEGADA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="MSC002",
     *                 description="Nave de llegada (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_FORWARDER",
     *                 type="string",
     *                 maxLength=50,
     *                 example="DHL Supply Chain",
     *                 description="Forwarder (opcional, máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_AGENCIA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Agencia Marítima ABC",
     *                 description="Agencia de aduanas (opcional, máx. 50 caracteres)."
     *             ),
     *
     *             
     *             @OA\Property(
     *                 property="CONTENEDOR",
     *                 type="array",
     *                 description="Lista de contenedores. Cada elemento debe incluir U_CONTENEDOR o U_SELLO.",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="U_CONTENEDOR",
     *                         type="string",
     *                         maxLength=50,
     *                         example="12312312",
     *                         description="Número de contenedor (opcional si se envía U_SELLO)."
     *                     ),
     *                     @OA\Property(
     *                         property="U_SELLO",
     *                         type="string",
     *                         maxLength=50,
     *                         example="33333",
     *                         description="Sello del contenedor (opcional si se envía U_CONTENEDOR)."
     *                     )
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="U_INTEGRACION",
     *                 type="string",
     *                 enum={"S","N"},
     *                 example="S",
     *                 description="Origen del dato: S (Integración) o N (Manual)."
     *             ),
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
     *                 description="Líneas de detalle de la orden de compra.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"ItemCode","Quantity","Price"},
     *                     @OA\Property(property="ItemCode", type="string", maxLength=50, example="0501010001639", description="Código del artículo (máx. 50 caracteres)."),
     *                     @OA\Property(property="U_COMPO_FINAL", type="string", maxLength=50, example="CF001", description="Composición final (opcional)."),
     *                     @OA\Property(property="Quantity", type="number", format="float", minimum=0.000001, example=100, description="Cantidad solicitada."),
     *                     @OA\Property(property="Currency", type="string", maxLength=3, example="CLP", description="Tipo de Divisa (máx. 3 caracteres)."),
     *                     @OA\Property(property="Price", type="number", format="float", minimum=0, example=250.00, description="Precio unitario."),
     *                     @OA\Property(property="TaxCode", type="string", maxLength=8, example="IVA", description="Código de impuesto (opcional)."),
     *                     @OA\Property(property="WhsCode", type="string", maxLength=8, example="01", description="Código de almacén (opcional)."),
     *                     @OA\Property(property="OcrCode", type="string", maxLength=8, example="", description="Centro de costo (opcional)."),
     *                     @OA\Property(property="OcrCode2", type="string", maxLength=8, example="", description="Proyecto (opcional)."),
     *                     @OA\Property(property="OcrCode3", type="string", maxLength=8, example="", description="Actividad (opcional)."),
     *                     @OA\Property(property="U_SEI_Aprobador", type="string", maxLength=20, example="Maria García", description="Usuario aprobador SEI (opcional)."),
     *                     @OA\Property(property="U_Integracion", type="string", enum={"S","N"}, example="S", description="Origen de la línea."),
     *                     @OA\Property(property="U_Status", type="string", enum={"Enviar","No enviar"}, example="Enviar", description="Estado de envío (opcional).")
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="CreateInvoice",
     *                 type="string",
     *                 enum={"tYES","tNO"},
     *                 example="tYES",
     *                 description="Indica si se debe crear una factura de reserva."
     *             ),
     *             @OA\Property(
     *                 property="InvoiceData",
     *                 type="object",
     *                 description="Datos para generar la factura de reserva (si `CreateInvoice = tYES`).",
     *                 @OA\Property(property="DocDate", type="string", format="date", example="2025-07-19", description="Fecha de la factura."),
     *                 @OA\Property(property="DocDueDate", type="string", format="date", example="2025-07-26", description="Fecha de vencimiento."),
     *                 @OA\Property(property="FolioPref", type="string", maxLength=4, example="FAC", description="Prefijo del folio (opcional)."),
     *                 @OA\Property(property="FolioNum", type="integer", example=12345, description="Número de folio."),
     *                 @OA\Property(property="U_Integracion", type="string", enum={"S","N"}, example="S", description="Origen de la factura."),
     *                 @OA\Property(property="U_Status", type="string", example="Enviar", description="Estado de la factura."),
     *                 @OA\Property(property="U_SEI_CARPETA", type="string", example="CARP001", description="Carpeta SEI asociada.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Orden de compra y factura de reserva creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orden de compra y Factura de reserva creada exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="oc",
     *                     type="object",
     *                     @OA\Property(property="DocEntry", type="integer", example=168),
     *                     @OA\Property(property="DocNum", type="integer", example=88)
     *                 ),
     *                 @OA\Property(
     *                     property="contenedor",
     *                     type="object",
     *                     @OA\Property(property="DocEntry", type="integer", example=22),
     *                     @OA\Property(property="DocNum", type="integer", example=22)
     *                 ),
     *                 @OA\Property(
     *                     property="invoice",
     *                     type="object",
     *                     @OA\Property(property="DocEntry", type="integer", example=94),
     *                     @OA\Property(property="DocNum", type="integer", example=43)
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-04T15:15:29-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=207,
     *         description="Orden de compra creada con errores parciales al generar contenedor o factura",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orden de Compra creada existosamente, pero ocurrió un error al generar factura de reserva."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="oc", type="object",
     *                     @OA\Property(property="DocEntry", type="integer", example=168),
     *                     @OA\Property(property="DocNum",   type="integer", example=88)
     *                 ),
     *                 @OA\Property(property="contenedor", type="object",
     *                     @OA\Property(property="error",       type="string", example="Error al crear CONTENEDOR"),
     *                     @OA\Property(property="error_code",  type="string", example="CONT_ERR"),
     *                     @OA\Property(property="suggestions", type="string", example="Verifique los datos de contenedor")
     *                 ),
     *                 @OA\Property(property="invoice", type="object",
     *                     @OA\Property(property="error",       type="string", example="Error al crear factura de reserva"),
     *                     @OA\Property(property="error_code",  type="string", example="INV_ERR"),
     *                     @OA\Property(property="suggestions", type="string", example="Verifique los datos de factura")
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-08-04T15:15:29-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Proveedor no existe","Artículo inválido"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear orden de compra"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Error de conexión con SAP"})
     *         )
     *     )
     * )
     */


    public function store(PurchaseOrderStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'purchase_orders',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );


        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear orden de compra: {$integrationLog['message']}",
                [],
                500
            );
        }
        try {
            $data = $this->purchaseOrdersService->preparePurchaseOrderData($request->validated());

            IntegrationLogger::update('purchase_orders', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
            ]);

            $response = $this->sapService->post('/PurchaseOrders', $data);

            IntegrationLogger::update('purchase_orders', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Orden de compra creada exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
            ]);
            $dataResponse['oc'] = [
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'DocNum' => $response['response']['DocNum'] ?? null
            ];

            $wmsService = new WmsApiService();
            $wmsResponse = $wmsService->makeRequest('POST', 'auth/oc_notify',  [
                'DocEntry' => $response['response']['DocEntry'] ?? null,
                'TipoDoc' => 'OC'
            ]);

            // ===========================
            // 2. Crear UDO CONTENEDOR (header + líneas)
            // ===========================
            $docEntryOC   = $response['response']['DocEntry'] ?? null;
            $docNumOC   = $response['response']['DocNum'] ?? null;
            Log::info(['ORDEN DE COMPRA CREADA' => $docEntryOC]);
            $containers   = $request->input('CONTENEDOR', []);

            if (!empty($containers) && $docEntryOC) {
                try {
                    $fContPayload = [
                        'Remark' => $docNumOC, //numero de OC que ve SAP
                        'F_CONTENEDOR_LINEACollection' => array_map(fn($ctr) => [
                            'U_CONTENEDOR'   => $ctr['U_CONTENEDOR'] ?? '',
                            'U_SELLO'        => $ctr['U_SELLO']      ?? '',
                            'U_DOC_ENTRY_PO' => $docEntryOC,
                        ], $containers),
                    ];

                    $responseContainer   = $this->sapService->post('/CONTENEDOR', $fContPayload);
                    $docEntryContainer   = $responseContainer['response']['DocEntry'] ?? null;

                    Log::info(['CONTENEDOR creado' => $responseContainer['response']]);
                    IntegrationLogger::update('purchase_orders', $integrationLog['data']->id, [
                        'message'               => 'CONTENEDOR UDO creado con líneas',
                        'request_body'          => json_encode($fContPayload, JSON_UNESCAPED_UNICODE),
                        'response'              => json_encode($responseContainer['response'], JSON_UNESCAPED_UNICODE),
                        'status_integration_id' => 3,
                    ]);

                    if ($docEntryContainer) {
                        // PATCH a PurchaseOrder para guardar el DocEntry de CONTENEDOR
                        $patchPayload = ['U_CONTENEDOR' => $docEntryContainer];
                        $patchResponse = $this->sapService->patch(
                            "/PurchaseOrders({$docEntryOC})",
                            $patchPayload
                        );
                        Log::info([
                            'PATCH PurchaseOrder' => [
                                'DocEntry' => $docEntryOC,
                                'payload'  => $patchPayload,
                                'response' => $patchResponse['response'] ?? null
                            ]
                        ]);
                        IntegrationLogger::update('purchase_orders', $integrationLog['data']->id, [
                            'message'               => 'PurchaseOrder actualizado con U_CONTENEDOR',
                            'request_body'          => json_encode($patchPayload, JSON_UNESCAPED_UNICODE),
                            'response'              => json_encode($patchResponse['response'], JSON_UNESCAPED_UNICODE),
                            'status_integration_id' => 3,
                        ]);
                        $dataResponse['contenedor'] = [
                            'DocEntry' => $docEntryContainer,
                            'DocNum' => $responseContainer['response']['DocNum'],
                        ];
                    }
                } catch (\Exception $e) {
                    $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());
                    Log::error('Error al crear CONTENEDOR: ' . $formattedException->message);

                    $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                        $formattedException->message,
                        $e->getCode()
                    );

                    IntegrationLogger::update('purchase_orders', $integrationLog['data']->id, [
                        'code'                  => $errorDetails['status_code'] ?? 400,
                        'request_body'          => $formattedException->request ?? json_encode($formattedException->request, JSON_UNESCAPED_UNICODE),
                        'message'               => $errorDetails['user_message'],
                        'response'              => json_encode($errorDetails, JSON_UNESCAPED_UNICODE),
                        'status_integration_id' => 4,
                    ]);

                    // Registramos el fallo pero no interrumpimos el flujo
                    $dataResponse['contenedor'] = [
                        'error' =>  $errorDetails['user_message'],
                        'error_code' => $errorDetails['error_code'],
                        'suggestions' => $errorDetails['suggestions'],
                        'original_error' => config('app.debug') ? $formattedException->message : null
                    ];
                }
            }

            // 4. Crear Factura de Reserva (PurchaseInvoice)
            if ($request->CreateInvoice == 'tYES') {

                // Registrar integración de invoice
                $invoiceLog = IntegrationLogger::create(
                    'reserve_invoices',
                    [
                        'service_name' => 'Crear',
                        'destiny' => 'SAP',
                        'status_integration_id' => 1,
                    ]
                );

                try {
                    // Generar payload de PurchaseInvoice
                    $invoiceData = $this->purchaseOrdersService->preparePurchaseInvoiceData($request, $response['response']);
                    $invoiceResponse = $this->sapService->post('/PurchaseInvoices', $invoiceData);

                    IntegrationLogger::update('reserve_invoices', $invoiceLog['data']->id, [
                        'code' => 201,
                        'message' => 'Factura de reserva creada exitosamente',
                        'request_body' => json_encode($invoiceResponse['request'], JSON_UNESCAPED_UNICODE),
                        'response' => json_encode($invoiceResponse['response'], JSON_UNESCAPED_UNICODE),
                        'status_integration_id' => 3,
                    ]);

                    $dataResponse['invoice'] = [
                        'DocEntry' => $invoiceResponse['response']['DocEntry'] ?? null,
                        'DocNum'   => $invoiceResponse['response']['DocNum'] ?? null
                    ];

                    return ApiResponse::success($dataResponse, 'Orden de compra y Factura de reserva creada exitosamente', 201);
                } catch (\Exception $e) {
                    $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

                    Log::error('Error al crear orden de factura de reserva: ' . $formattedException->message);

                    // Usar el manejador de errores mejorado
                    $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                        $formattedException->message,
                        $e->getCode()
                    );

                    IntegrationLogger::update('reserve_invoices', $integrationLog['data']->id, [
                        'code' => $errorDetails['status_code'] ?? 400,
                        'request_body' => $formattedException->request ?? json_encode($formattedException->request, JSON_UNESCAPED_UNICODE),
                        'message' => $errorDetails['user_message'],
                        'response' => $errorDetails,
                        'status_integration_id' => 4,
                    ]);

                    $dataResponse['invoice'] = [
                        'error' =>  $errorDetails['user_message'],
                        'error_code' => $errorDetails['error_code'],
                        'suggestions' => $errorDetails['suggestions'],
                        'original_error' => config('app.debug') ? $formattedException->message : null
                    ];

                    return ApiResponse::success(
                        $dataResponse,
                        'Orden de Compra creado existosamente, pero ocurrió un error al generar factura de reserva. ' . $errorDetails['user_message'],
                        207
                    );
                }
            }


            return ApiResponse::success($dataResponse, 'Orden de compra creada exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear orden de compra: ' . $formattedException->message);

            // Usar el manejador de errores mejorado
            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            IntegrationLogger::update('purchase_orders', $integrationLog['data']->id, [
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
