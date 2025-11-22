<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\BusinessPartnerStoreRequest;
use App\Http\Requests\Sap\BusinessPartnerUpdateRequest;
use App\Http\Resources\BusinessPartnerResource;
use App\Services\BusinessPartnersService;
use App\Services\SapErrorHandlerService;
use App\Services\SapServiceLayerService;
use App\Services\WmsApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class BusinessPartnersController extends Controller
{
    protected $sapService;
    protected $wmsService;
    protected $businessPartners;

    public function __construct(
        SapServiceLayerService $sapService,
        WmsApiService $wmsService,
        BusinessPartnersService $businessPartners
    ) {
        $this->sapService = $sapService;
        $this->wmsService = $wmsService;
        $this->businessPartners = $businessPartners;
    }

    /**
     * @OA\Get(
     *     path="/socios",
     *     summary="Obtener lista de socios de negocio con filtros y paginación",
     *     tags={"Socios de Negocios"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Tipo de socio de negocio: cCustomer (Cliente), cSupplier (Proveedor), cLid (Lead).",
     *         required=true,
     *         @OA\Schema(type="string", enum={"cCustomer", "cSupplier", "cLid"}, example="Customer")
     *     ),
     *     @OA\Parameter(
     *         name="cardCode",
     *         in="query",
     *         description="Código del socio de negocio",
     *         required=false,
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de socios por página (entre 1 y 1000)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=1000, example=20)
     *     ),
     *     @OA\Parameter(
     *         name="current_page",
     *         in="query",
     *         description="Número de página actual (empezando en 1)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de socios de negocio obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Socios de negocio obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="CardCode", type="string", example="PCN000123", description="Código único del socio de negocio."),
     *                     @OA\Property(property="CardName", type="string", example="Proveedor Internacional SA", description="Nombre o razón social del socio."),
     *                     @OA\Property(property="CardType", type="string", example="cSupplier", description="cCustomer (Cliente), cSupplier (Proveedor), cLid (Lead)."),
     *                     @OA\Property(property="GroupCode", type="integer", example=108, description="Código del grupo de socios."),
     *                     @OA\Property(property="Currency", type="string", example="USD", description="Moneda utilizada por el socio."),
     *                     @OA\Property(property="FederalTaxID", type="string", example="55555555-5", description="RUT del socio."),
     *                     @OA\Property(property="Phone1", type="string", example="+56 9 8765 4321", description="Teléfono de contacto."),
     *                     @OA\Property(property="MailAddress", type="string", example="contacto@proveedor.com", description="Correo electrónico principal."),
     *                     @OA\Property(property="Notes", type="string", example="Importador de textiles", description="Giro o comentario adicional del socio."),
     *                     @OA\Property(property="GRouoNum", type="integer", example=-1, description="Condiciones de pago."),
     *                     @OA\Property(property="PriceListNum", type="integer", example=1, description="Número de la lista de precios asociada."),
     *                     @OA\Property(property="DebitorAccount", type="string", example="2012100040", description="Cuenta contable asociada."),
     *                     @OA\Property(property="Valid", type="string", example="tYES", description="Indica si el socio está activo (tYES o tNO)."),
     *                     @OA\Property(property="U_INTEGRACION", type="string", example="FMMS", description="Origen del dato (ej. FMMS, SAP, etc)."),
     *                     @OA\Property(
     *                         property="BPAddresses",
     *                         type="array",
     *                         description="Direcciones asociadas al socio de negocio",
     *                         @OA\Items(
     *                             @OA\Property(property="AddressName", type="string", example="Oficina Central", description="Nombre o etiqueta de la dirección."),
     *                             @OA\Property(property="Street", type="string", example="Calle Falsa 123", description="Dirección física."),
     *                             @OA\Property(property="City", type="string", example="Shenzhen", description="Ciudad."),
     *                             @OA\Property(property="County", type="string", example="Nanshan", description="Comuna o condado."),
     *                             @OA\Property(property="Country", type="string", example="CN", description="Código del país (ISO Alpha-2)."),
     *                             @OA\Property(property="AddressType", type="string", example="bo_BillTo", description="Tipo de dirección (bo_BillTo, bo_ShipTo, etc).")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="BPBankAccounts",
     *                         type="array",
     *                         description="Cuentas bancarias del socio",
     *                         @OA\Items(
     *                             @OA\Property(property="LogInstance", type="integer", example=1, description="Identificador interno del registro."),
     *                             @OA\Property(property="BankCode", type="string", example="049", description="Nombre o código del banco."),
     *                             @OA\Property(property="AccountNo", type="string", example="123456789", description="Número de cuenta bancaria.")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="ContactEmployees",
     *                         type="array",
     *                         description="Contactos asociados al socio",
     *                         @OA\Items(
     *                             @OA\Property(property="Name", type="string", example="Juan Pérez", description="Nombre completo del contacto."),
     *                             @OA\Property(property="Position", type="string", example="Gerente de Compras", description="Cargo dentro de la empresa."),
     *                             @OA\Property(property="Phone1", type="string", example="+86 138 0000 0000", description="Teléfono del contacto."),
     *                             @OA\Property(property="E_Mail", type="string", example="juan.perez@proveedor.com", description="Correo electrónico del contacto.")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=20, description="Cantidad de registros por página."),
     *             @OA\Property(property="current_page", type="integer", example=1, description="Página actual."),
     *             @OA\Property(property="total_items", type="integer", example=83, description="Cantidad total de socios encontrados."),
     *             @OA\Property(property="total_pages", type="integer", example=5, description="Cantidad total de páginas disponibles."),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-21T10:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener socios de negocio"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Connection timeout"}),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-21T10:00:00-04:00")
     *         )
     *     )
     * )
     */

    public function index(Request $request): JsonResponse
    {
        try {
            $params = [];
            $filters = [];

            // --- Filtros dinámicos ---
            if ($request->filled('type')) {
                $filters[] = "CardType eq '{$request->type}'";
            }

            if ($request->filled('cardCode')) {
                $filters[] = "CardCode eq '{$request->cardCode}'";
            }

            if (!empty($filters)) {
                $params['$filter'] = implode(' and ', $filters);
            }

            // --- Paginación ---
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params['$top'] = $perPage;
            $params['$skip'] = $skip;

            // --- Campos y orden ---
            $params['$select'] = 'CardCode,CardName,CardType,GroupCode,Currency,FederalTaxID,Phone1,MailAddress,Notes,PayTermsGrpCode,PriceListNum,DebitorAccount,Valid,U_INTEGRACION,BPAddresses,BPBankAccounts,ContactEmployees';
            $params['$orderby'] = 'CardCode asc';

            // --- Obtener página actual ---
            $response = $this->sapService->get('/BusinessPartners', $params);
            $partners = $response['response']['value'] ?? [];

            // --- Obtener total seguro ---
            $totalItems = null;
            try {
                $countUrl = "/BusinessPartners/\$count";
                $countParams = [];

                if (isset($params['$filter'])) {
                    $countParams['$filter'] = $params['$filter'];
                }

                $countResponse = $this->sapService->get($countUrl, $countParams);
                $totalItems = (int) ($countResponse['response'] ?? 0);
            } catch (\Throwable $t) {
                Log::warning("No se pudo obtener el total de socios de negocio: " . $t->getMessage());
                $totalItems = count($partners); // fallback
            }

            // --- Respuesta final ---
            return ApiResponse::success([
                'data' => BusinessPartnerResource::collection(collect($partners)),
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => $perPage > 0 ? ceil($totalItems / $perPage) : 1,
            ], 'Socios de negocio obtenidos exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener socios de negocio: ' . $formattedException->message);

            return ApiResponse::error(
                'Error al obtener socios de negocio',
                [$formattedException->message],
                500
            );
        }
    }



    /**
     * @OA\Get(
     *     path="/socios/{cardCode}",
     *     summary="Obtener socio de negocio específico",
     *     tags={"Socios de Negocios"},
     *     @OA\Parameter(
     *         name="cardCode",
     *         in="path",
     *         description="Código del socio de negocio",
     *         required=true,
     *         @OA\Schema(type="string", example="C00001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Socio de negocio obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Socio de negocio obtenido exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="CardCode", type="string", example="C00001", description="Código único del socio de negocio."),
     *                 @OA\Property(property="CardName", type="string", example="Cliente Ejemplo S.A.", description="Nombre o razón social del socio."),
     *                 @OA\Property(property="CardType", type="string", example="C", description="Tipo: S (Proveedor), C (Cliente), L (Lead)."),
     *                 @OA\Property(property="GroupCode", type="integer", example=100, description="Código del grupo de socio."),
     *                 @OA\Property(property="Currency", type="string", example="CLP", description="Moneda del socio."),
     *                 @OA\Property(property="LicTradNum", type="string", example="12345678-9", description="RUT del socio."),
     *                 @OA\Property(property="Phone1", type="string", example="+56912345678", description="Teléfono de contacto."),
     *                 @OA\Property(property="E_Mail", type="string", example="contacto@cliente.com", description="Correo electrónico del socio."),
     *                 @OA\Property(property="Notes", type="string", example="Empresa dedicada a importaciones", description="Giro o comentario adicional."),
     *                 @OA\Property(property="GRouoNum", type="integer", example=-1, description="Condición de pago."),
     *                 @OA\Property(property="ListNum", type="integer", example=1, description="Lista de precio asociada."),
     *                 @OA\Property(property="DebPayAcct", type="string", example="2012100040", description="Cuenta contable asociada."),
     *                 @OA\Property(property="U_INTEGRACION", type="string", example="S", description="Origen del dato: S (Integración), N (Manual)."),
     *
     *                 @OA\Property(
     *                     property="BPAddresses",
     *                     type="array",
     *                     description="Direcciones asociadas al socio",
     *                     @OA\Items(
     *                         @OA\Property(property="AddressName", type="string", example="Dirección Principal", description="Nombre o alias de la dirección."),
     *                         @OA\Property(property="Street", type="string", example="Av. Providencia 123", description="Dirección física."),
     *                         @OA\Property(property="City", type="string", example="Santiago", description="Ciudad."),
     *                         @OA\Property(property="County", type="string", example="Providencia", description="Comuna."),
     *                         @OA\Property(property="Country", type="string", example="CL", description="País (código alfa-2 ISO).")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="BPBankAccounts",
     *                     type="array",
     *                     description="Cuentas bancarias asociadas",
     *                     @OA\Items(
     *                         @OA\Property(property="BankCode", type="string", example="BANCOESTADO", description="Código del banco."),
     *                         @OA\Property(property="Account", type="string", example="987654321", description="Número de cuenta.")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="ContactEmployees",
     *                     type="array",
     *                     description="Contactos asociados al socio",
     *                     @OA\Items(
     *                         @OA\Property(property="Name", type="string", example="Juan Pérez", description="Nombre del contacto."),
     *                         @OA\Property(property="Position", type="string", example="Encargado de compras", description="Cargo."),
     *                         @OA\Property(property="Tel", type="string", example="+56 9 8765 4321", description="Teléfono del contacto."),
     *                         @OA\Property(property="E_Mail", type="string", example="juan.perez@cliente.com", description="Correo electrónico del contacto.")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Socio de negocio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Socio de negocio no encontrado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-30T15:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener socio de negocio"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-30T15:01:00-04:00")
     *         )
     *     )
     * )
     */
    public function show(string $cardCode): JsonResponse
    {
        try {
            $params = [
                '$select' => 'CardCode,CardName,CardType,GroupCode,Currency,FederalTaxID,Phone1,MailAddress,Notes,PayTermsGrpCode,PriceListNum,DebitorAccount,Valid,U_INTEGRACION,BPAddresses,BPBankAccounts,ContactEmployees',
            ];

            $response = $this->sapService->get("/BusinessPartners('{$cardCode}')", $params);

            return ApiResponse::success(
                new BusinessPartnerResource($response['response']),
                'Socio de negocio obtenido exitosamente'
            );
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error("Error al obtener socio de negocio {$cardCode}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Socio de negocio no encontrado' : 'Error al obtener socio de negocio';

            return ApiResponse::error(
                $message,
                [$formattedException->message],
                $statusCode
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/socios/crear",
     *     summary="Crear socio de negocio en SAP desde FMMS",
     *     tags={"Socios de Negocios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "CardCode", "CardName", "CardType", "GroupCode", "Currency", "LicTradNum", "Phone1",
     *                 "E_Mail", "Address", "Street", "City", "Country", "BankCode", "Account",
     *                 "U_INTEGRACION", "Contact"
     *             },
     *             @OA\Property(
     *                 property="CardCode",
     *                 type="string",
     *                 maxLength=15,
     *                 example="PCN000123",
     *                 description="Código único del socio de negocio (máx. 15 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="CardName",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Proveedor Internacional SA",
     *                 description="Nombre o razón social del socio de negocio (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="CardType",
     *                 type="string",
     *                 enum={"cCustomer","cSupplier","cLid"},
     *                 example="cSupplier",
     *                 description="Tipo de socio de negocio: cCustomer (Cliente), cSupplier (Proveedor), cLid (Lead)."
     *             ),
     *             @OA\Property(
     *                 property="GroupCode",
     *                 type="integer",
     *                 example=108,
     *                 description="Código del grupo de socio de negocio (ej: 100=local, 108=extranjero)."
     *             ),
     *             @OA\Property(
     *                 property="Currency",
     *                 type="string",
     *                 maxLength=3,
     *                 example="CLP",
     *                 description="Moneda asociada (máx. 3 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="LicTradNum",
     *                 type="string",
     *                 maxLength=32,
     *                 example="55555555-5",
     *                 description="RUT o identificación tributaria (máx. 32 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Phone1",
     *                 type="string",
     *                 maxLength=20,
     *                 example="+56 9 8765 4321",
     *                 description="Número de teléfono (máx. 20 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="E_Mail",
     *                 type="string",
     *                 maxLength=100,
     *                 example="contacto@proveedor.com",
     *                 description="Correo electrónico del socio (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Notes",
     *                 type="string",
     *                 maxLength=200,
     *                 example="Importador de textiles",
     *                 description="Notas o giro del negocio (máx. 200 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="GRouoNum",
     *                 type="integer",
     *                 example=-1,
     *                 description="Código de condiciones de pago (opcional)."
     *             ),
     *             @OA\Property(
     *                 property="ListNum",
     *                 type="integer",
     *                 example=1,
     *                 description="Código de la lista de precios (opcional)."
     *             ),
     *             @OA\Property(
     *                 property="DebPayAcct",
     *                 type="string",
     *                 maxLength=50,
     *                 example="2012100040",
     *                 description="Cuenta contable asignada (máx. 50 caracteres, opcional)."
     *             ),
     *             @OA\Property(
     *                 property="U_INTEGRACION",
     *                 type="string",
     *                 enum={"S","N"},
     *                 example="S",
     *                 description="Origen del dato: S (Integración) o N (Manual)."
     *             ),
     *             @OA\Property(
     *                 property="Address",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Oficina Central",
     *                 description="Identificador de dirección (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Street",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Calle Falsa 123",
     *                 description="Calle y número (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="City",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Shenzhen",
     *                 description="Ciudad (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="County",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Nanshan",
     *                 description="Comuna o condado (máx. 100 caracteres, opcional)."
     *             ),
     *             @OA\Property(
     *                 property="Country",
     *                 type="string",
     *                 maxLength=2,
     *                 example="CN",
     *                 description="Código del país (ISO alfa-2, máx. 2 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="BankCode",
     *                 type="string",
     *                 maxLength=30,
     *                 example="049",
     *                 description="Código del banco (máx. 30 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Account",
     *                 type="string",
     *                 maxLength=50,
     *                 example="123456789",
     *                 description="Número de cuenta (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ORIGEN_PETICION",
     *                 type="string",
     *                 maxLength=15,
     *                 example="FMMS",
     *                 description="Origen de donde se realiza la petición FMMS, WMS, ETC."
     *             ),
     *             @OA\Property(
     *                 property="Contact",
     *                 type="object",
     *                 required={"Name"},
     *                 description="Persona de contacto del socio de negocio.",
     *                 @OA\Property(
     *                     property="Name",
     *                     type="string",
     *                     maxLength=50,
     *                     example="Juan Pérez",
     *                     description="Nombre del contacto (máx. 50 caracteres)."
     *                 ),
     *                 @OA\Property(
     *                     property="Position",
     *                     type="string",
     *                     maxLength=90,
     *                     example="Gerente de Compras",
     *                     description="Cargo o posición (máx. 90 caracteres)."
     *                 ),
     *                 @OA\Property(
     *                     property="Tel",
     *                     type="string",
     *                     maxLength=20,
     *                     example="+86 138 0000 0000",
     *                     description="Teléfono del contacto (máx. 20 caracteres)."
     *                 ),
     *                 @OA\Property(
     *                     property="E_Mail",
     *                     type="string",
     *                     maxLength=100,
     *                     example="juan.perez@proveedor.com",
     *                     description="Correo del contacto (máx. 100 caracteres)."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Socio de negocio creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Socio de negocio creado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"CardCode": "PCN000123", "CardName": "Proveedor Internacional SA"}
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-29T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"El código del socio de negocio es obligatorio."}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-29T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflicto - Socio ya existe",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El socio de negocio ya existe"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear socio de negocio"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function store(BusinessPartnerStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'business_partners',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al crear socio de negocio: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->businessPartners->prepareBusinessPartnerData($request->validated());
            $wmsData = $this->businessPartners->prepareBusinessPartnerWmsData($request->validated());

            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
                'includes_wms_integration' => true
            ]);

            $response = $this->sapService->post('/BusinessPartners', $data);
            Log::info($response);

            // Si no falla envio a SAP, enviar a WMS
            $wmsResponse = $this->wmsService->makeRequest('POST', 'auth/create_sn', $wmsData);

            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
                'code' => 201,
                'message' => 'Artículo creado exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
                'wms_request_body' => json_encode($this->wmsService->lastRequest),
                'wms_code' => $wmsResponse['status_code'],
                'wms_response' => json_encode($wmsResponse['body']),
            ]);

            return ApiResponse::success([
                'CardCode' => $data['CardCode'],
                'CardName' => $data['CardName']
            ], 'Socio de negocio creado exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear socio de negocio: ' . $formattedException->message, [
                'request_data' => $request->validated(),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            \App\Services\SapErrorHandlerService::setRequestContext($request->all());
            // Usar el manejador de errores mejorado
            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
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

    /**
     * @OA\Patch(
     *     path="/socios/{CardCode}",
     *     summary="Actualizar socio de negocio existente en SAP",
     *     tags={"Socios de Negocios"},
     *     @OA\Parameter(
     *         name="CardCode",
     *         in="path",
     *         required=true,
     *         description="Código único del socio de negocio a actualizar (máx. 15 caracteres).",
     *         @OA\Schema(type="string", example="PCN000123")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="CardName",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Proveedor Internacional S.A.",
     *                 description="Nombre o razón social del socio de negocio (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="GroupCode",
     *                 type="integer",
     *                 enum={100, 108},
     *                 example=100,
     *                 description="Código del grupo de socio de negocio (100=local, 108=extranjero)."
     *             ),
     *             @OA\Property(
     *                 property="Currency",
     *                 type="string",
     *                 maxLength=3,
     *                 example="CLP",
     *                 description="Moneda del socio de negocio (máx. 3 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="LicTradNum",
     *                 type="string",
     *                 maxLength=32,
     *                 example="55555555-5",
     *                 description="RUT o identificación tributaria (máx. 32 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Phone1",
     *                 type="string",
     *                 maxLength=20,
     *                 example="+56 9 1111 2222",
     *                 description="Teléfono principal (máx. 20 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="E_Mail",
     *                 type="string",
     *                 maxLength=100,
     *                 example="nuevo@proveedor.com",
     *                 description="Correo electrónico del socio de negocio (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Notes",
     *                 type="string",
     *                 maxLength=200,
     *                 example="Importador de textiles y confecciones",
     *                 description="Notas o giro comercial (máx. 200 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="PayTermsGrp",
     *                 type="integer",
     *                 example=45,
     *                 description="Código de condiciones de pago (opcional)."
     *             ),
     *             @OA\Property(
     *                 property="ListNum",
     *                 type="integer",
     *                 example=2,
     *                 description="Código de la lista de precios (opcional)."
     *             ),
     *             @OA\Property(
     *                 property="DebPayAcct",
     *                 type="string",
     *                 maxLength=50,
     *                 example="2012100040",
     *                 description="Cuenta contable asignada (máx. 50 caracteres, opcional)."
     *             ),
     *             @OA\Property(
     *                 property="Address",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Nueva Oficina",
     *                 description="Identificador de dirección (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="Street",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Avenida Nueva 456",
     *                 description="Calle y número (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="City",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Shanghai",
     *                 description="Ciudad (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="County",
     *                 type="string",
     *                 maxLength=100,
     *                 example="Pudong",
     *                 description="Comuna o condado (máx. 100 caracteres, opcional)."
     *             ),
     *             @OA\Property(
     *                 property="Country",
     *                 type="string",
     *                 maxLength=2,
     *                 example="CN",
     *                 description="Código del país (ISO alfa-2, máx. 2 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ORIGEN_PETICION",
     *                 type="string",
     *                 maxLength=15,
     *                 example="FMMS",
     *                 description="Origen de donde se realiza la petición FMMS, WMS, ETC."
     *             ),
     *             @OA\Property(
     *                 property="Contact",
     *                 type="object",
     *                 description="Información del contacto principal.",
     *                 @OA\Property(
     *                     property="Name",
     *                     type="string",
     *                     maxLength=50,
     *                     example="María González",
     *                     description="Nombre del contacto (máx. 50 caracteres)."
     *                 ),
     *                 @OA\Property(
     *                     property="Position",
     *                     type="string",
     *                     maxLength=90,
     *                     example="Gerente General",
     *                     description="Cargo o posición (máx. 90 caracteres)."
     *                 ),
     *                 @OA\Property(
     *                     property="Tel",
     *                     type="string",
     *                     maxLength=20,
     *                     example="+86 139 1111 2222",
     *                     description="Teléfono del contacto (máx. 20 caracteres)."
     *                 ),
     *                 @OA\Property(
     *                     property="E_Mail",
     *                     type="string",
     *                     maxLength=100,
     *                     example="maria.gonzalez@proveedor.com",
     *                     description="Correo del contacto (máx. 100 caracteres)."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Socio de negocio actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Socio de negocio actualizado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="CardCode", type="string", example="PCN000123"),
     *                 @OA\Property(
     *                     property="updated_fields",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"CardName", "Phone1", "E_Mail"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Socio de negocio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Socio de negocio no encontrado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */

    public function update(string $cardCode, Request $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'business_partners',
            [
                'service_name' => 'Actualizar',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al actualizar socio de negocio: {$integrationLog['message']}",
                [],
                500
            );
        }
        // Inyectar el CardCode de la ruta al request para validación
        $request->merge(['CardCode' => $cardCode]);

        $validateRequest = new BusinessPartnerUpdateRequest();

        $rules = $validateRequest->rules();
        $messages = method_exists($validateRequest, 'messages') ? $validateRequest->messages() : [];

        $data = Validator::make($request->all(), $rules, $messages);

        if ($data->fails()) {
            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
                'code' => 422,
                'message' => 'Error al actualizar socio de negocio',
                'response' => json_encode($data->errors()->toArray()),
                'status_integration_id' => 4,
            ]);

            return ApiResponse::error(
                'Error de validación',
                $data->errors()->toArray(),
                422
            );
        }

        try {
            $validatedData = $data->validated();
            $data = $this->businessPartners->prepareBusinessPartnerDataForUpdate($validatedData);
            $wmsData = $this->businessPartners->prepareBusinessPartnerWmsData($validatedData);

            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
                'includes_wms_integration' => true,
            ]);

            // Escapar el CardCode para seguridad
            $escapedCardCode = htmlspecialchars($cardCode, ENT_QUOTES);
            $response = $this->sapService->patch("/BusinessPartners('{$escapedCardCode}')", $data);

            // Si no falla envio a SAP, enviar a WMS
            $wmsResponse = $this->wmsService->makeRequest('POST', 'auth/create_sn', $wmsData);

            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
                'code' => 204,
                'message' => 'Artículo actualizado exitosamente',
                'request_body' => json_encode($response['request'], JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response['response'], JSON_UNESCAPED_UNICODE),
                'status_integration_id' => 3,
                'wms_request_body' => json_encode($this->wmsService->lastRequest),
                'wms_code' => $wmsResponse['status_code'],
                'wms_response' => json_encode($wmsResponse['body']),
            ]);

            return ApiResponse::success([
                'CardCode' => $cardCode,
                'updated_fields' => array_keys($data)
            ], 'Socio de negocio actualizado exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al actualizar socio de negocio: ' . $formattedException->message, [
                'card_code' => $cardCode,
                'request_data' => $data,
                'user_id' => auth()->id()
            ]);

            \App\Services\SapErrorHandlerService::setRequestContext($request->all());
            $errorDetails = SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            IntegrationLogger::update('business_partners', $integrationLog['data']->id, [
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
                    'suggestions' => $errorDetails['suggestions']
                ],
                $errorDetails['status_code']
            );
        }
    }
}
