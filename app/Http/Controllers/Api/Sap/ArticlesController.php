<?php

namespace App\Http\Controllers\Api\Sap;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sap\ArticleSendBatchesRequest;
use App\Http\Requests\Sap\ArticleStoreRequest;
use App\Http\Requests\Sap\ArticleUpdateRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Integration;
use App\Services\ArticlesService;
use App\Services\SapArticlesService;
use App\Services\SapServiceLayerService;
use App\Services\WmsApiService;
use Exception;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Tymon\JWTAuth\Contracts\Providers\Auth;

/**
 * @OA\Tag(
 *     name="Artículos",
 *     description="Gestión de artículos en SAP"
 * )
 */
class ArticlesController extends Controller
{
    protected $sapService;
    protected $wmsService;
    protected $articleService;

    public function __construct(
        SapServiceLayerService $sapService,
        WmsApiService $wmsService,
        ArticlesService $articleService
    ) {
        $this->sapService = $sapService;
        $this->wmsService = $wmsService;
        $this->articleService = $articleService;
    }

    /**
     * Obtener lista de artículos
     */

    /**
     * @OA\Get(
     *     path="/articulos",
     *     summary="Obtener lista de artículos con filtros y paginación",
     *     tags={"Artículos"},
     *     @OA\Parameter(
     *         name="itemCode",
     *         in="query",
     *         description="Filtrar por código exacto del artículo",
     *         required=false,
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="AssetItem",
     *         in="query",
     *         description="Filtrar si el artículo es un activo fijo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"tYES", "tNO"}, example="")
     *     ),
     *     @OA\Parameter(
     *         name="ItmsGrpCode",
     *         in="query",
     *         description="Filtrar por código de grupo de artículos",
     *         required=false,
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de artículos por página",
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
     *         description="Lista de artículos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Artículos obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="ItemCode", type="string", example="ART12345"),
     *                         @OA\Property(property="ItemName", type="string", example="Pantalón Mujer Tiro Alto"),
     *                         @OA\Property(property="ItemType", type="string", example="I"),
     *                         @OA\Property(property="ItmsGrpCode", type="integer", example=101),
     *                         @OA\Property(property="UgpEntry", type="integer", example=-1),
     *                         @OA\Property(property="InvntItem", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                         @OA\Property(property="SellItem", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                         @OA\Property(property="PrchseItem", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                         @OA\Property(property="ManageStockByWarehouse", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                         @OA\Property(property="SWW", type="string", example="SKU98765"),
     *                         @OA\Property(property="BuyUnitMsr", type="string", example="UN"),
     *                         @OA\Property(property="SalUnitMsr", type="string", example="UN"),
     *                         @OA\Property(property="PurPackUn", type="integer", example="1"),
     *                         @OA\Property(property="U_NEGOCIO", type="string", example="Retail"),
     *                         @OA\Property(property="U_DEPARTAMENTO", type="string", example="Damas"),
     *                         @OA\Property(property="U_LINEA", type="string", example="Ropa Casual"),
     *                         @OA\Property(property="U_CLASE", type="string", example="Jeans"),
     *                         @OA\Property(property="U_SERIE", type="string", example="SER2025"),
     *                         @OA\Property(property="U_CONTINUIDAD", type="string", example="S"),
     *                         @OA\Property(property="U_TEMPORADA", type="string", example="VER25"),
     *                         @OA\Property(property="U_MARCA", type="string", example="Levis"),
     *                         @OA\Property(property="U_COMPO", type="string", example="100% algodón"),
     *                         @OA\Property(property="U_INTEGRACION", type="string", example="S"),
     *                         @OA\Property(property="U_ANO_CREACION", type="integer", example=2025),
     *                         @OA\Property(property="U_PROCEDENCIA", type="string", example="Importado"),
     *                         @OA\Property(property="CreateDate", type="string", format="date", example="2025-07-18"),
     *                         @OA\Property(property="UpdateDate", type="string", format="date", example="2025-07-18")
     *                     )
     *                 ),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=83),
     *                 @OA\Property(property="total_pages", type="integer", example=5)
     *             ),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-18T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener los artículos"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Connection timeout"}),
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-07-18T22:47:48-04:00")
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
            if ($request->filled('itemCode')) {
                $filters[] = "ItemCode eq '{$request->itemCode}'";
            }

            if ($request->filled('AssetItem') && in_array($request->AssetItem, ['tYES', 'tNO'])) {
                $filters[] = "AssetItem eq '{$request->AssetItem}'";
            }

            if ($request->filled('ItmsGrpCode')) {
                $filters[] = "ItemsGroupCode eq {$request->ItmsGrpCode}";
            }

            if (count($filters) > 0) {
                $params['$filter'] = implode(' and ', $filters);
            }

            // --- Paginación ---
            $perPage = (int) $request->input('per_page', 20);
            $currentPage = (int) $request->input('current_page', 1);
            $skip = ($currentPage - 1) * $perPage;

            $params['$top'] = $perPage;
            $params['$skip'] = $skip;

            // --- Campos a seleccionar ---
            $params['$select'] = 'ItemCode,ItemName,ItemType,ItemsGroupCode,UoMGroupEntry,InventoryItem,SalesItem,PurchaseQtyPerPackUnit,PurchaseItem,ManageStockByWarehouse,SWW,PurchaseUnit,SalesUnit,User_Text,U_NEGOCIO,U_DEPARTAMENTO,U_LINEA,U_CLASE,U_SERIE,U_CONTINUIDAD,U_TEMPORADA,U_MARCA,U_COMPO,U_INTEGRACION,U_ANO_CREACION,U_PROCEDENCIA,CreateDate,UpdateDate';

            // --- Obtener los ítems ---
            $response = $this->sapService->get('/Items', $params);
            $items = $response['response']['value'] ?? [];

            // --- Obtener total con /$count (sin bloquear) ---
            $totalItems = null;
            try {
                $countUrl = "/Items/\$count";
                $countParams = [];

                if (isset($params['$filter'])) {
                    $countParams['$filter'] = $params['$filter'];
                }

                $countResponse = $this->sapService->get($countUrl, $countParams);
                $totalItems = (int) ($countResponse['response'] ?? 0);
            } catch (\Throwable $t) {
                Log::warning("No se pudo obtener el total de ítems: " . $t->getMessage());
                $totalItems = count($items); // fallback
            }

            // --- Mapear datos con Resource ---
            $itemsMaps = ArticleResource::collection($items);

            // --- Respuesta final ---
            return ApiResponse::success([
                'items' => $itemsMaps,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_items' => $totalItems,
                'total_pages' => $perPage > 0 ? ceil($totalItems / $perPage) : 1,
            ], 'Artículos obtenidos exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener artículos: ' . $formattedException->message);

            return ApiResponse::error(
                'Error al obtener los artículos',
                [$formattedException->message],
                500
            );
        }
    }


    /**
     * Obtener un artículo específico
     */

    /**
     * @OA\Get(
     *     path="/articulos/{itemCode}",
     *     summary="Obtener un artículo específico por código",
     *     tags={"Artículos"},
     *     @OA\Parameter(
     *         name="itemCode",
     *         in="path",
     *         description="Código del artículo a obtener",
     *         required=true,
     *         @OA\Schema(type="string", example="ART12345")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Artículo obtenido exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="ItemCode", type="string", example="ART12345"),
     *                 @OA\Property(property="ItemName", type="string", example="Pantalón Mujer Tiro Alto"),
     *                 @OA\Property(property="ItemType", type="string", example="I"),
     *                 @OA\Property(property="ItmsGrpCode", type="integer", example=101),
     *                 @OA\Property(property="UgpEntry", type="integer", example=-1),
     *                 @OA\Property(property="InvntItem", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                 @OA\Property(property="SellItem", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                 @OA\Property(property="PrchseItem", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                 @OA\Property(property="ManageStockByWarehouse", type="string", enum={"tYES", "tNO"}, example="tYES"),
     *                 @OA\Property(property="SWW", type="string", example="SKU98765"),
     *                 @OA\Property(property="BuyUnitMsr", type="string", example="UN"),
     *                 @OA\Property(property="SalUnitMsr", type="string", example="UN"),
     *                 @OA\Property(property="PurPackUn", type="integer", example="1"),
     *                 @OA\Property(property="U_NEGOCIO", type="string", example="Retail"),
     *                 @OA\Property(property="U_DEPARTAMENTO", type="string", example="Damas"),
     *                 @OA\Property(property="U_LINEA", type="string", example="Ropa Casual"),
     *                 @OA\Property(property="U_CLASE", type="string", example="Jeans"),
     *                 @OA\Property(property="U_SERIE", type="string", example="SER2025"),
     *                 @OA\Property(property="U_CONTINUIDAD", type="string", enum={"S","N"}, example="S"),
     *                 @OA\Property(property="U_TEMPORADA", type="string", example="VER25"),
     *                 @OA\Property(property="U_MARCA", type="string", example="Levis"),
     *                 @OA\Property(property="U_COMPO", type="string", example="100% algodón"),
     *                 @OA\Property(property="U_INTEGRACION", type="string", enum={"S","N"}, example="S"),
     *                 @OA\Property(property="U_ANO_CREACION", type="integer", example=2025),
     *                 @OA\Property(property="U_PROCEDENCIA", type="string", example="Importado"),
     *                 @OA\Property(property="CreateDate", type="string", format="date", example="2025-07-18"),
     *                 @OA\Property(property="UpdateDate", type="string", format="date", example="2025-07-18"),
     *                 @OA\Property(
     *                     property="Inventory",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="WhsCode", type="string", example="01"),
     *                         @OA\Property(property="InStock", type="number", example=50),
     *                         @OA\Property(property="Committed", type="number", example=5),
     *                         @OA\Property(property="Ordered", type="number", example=10),
     *                         @OA\Property(property="MinStock", type="number", example=10),
     *                         @OA\Property(property="MaxStock", type="number", example=100),
     *                         @OA\Property(property="Available", type="number", example=45)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artículo no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Artículo no encontrado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Item not found"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener el artículo"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"Database connection error"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     )
     * )
     */

    public function show(string $itemCode): JsonResponse
    {
        try {
            $params = [
                '$select' => 'ItemCode,ItemName,ItemType,ItemsGroupCode,UoMGroupEntry,InventoryItem,SalesItem,PurchaseQtyPerPackUnit,PurchaseItem,ManageStockByWarehouse,SWW,PurchaseUnit,SalesUnit,User_Text,U_NEGOCIO,U_DEPARTAMENTO,U_LINEA,U_CLASE,U_SERIE,U_CONTINUIDAD,U_TEMPORADA,U_MARCA,U_COMPO,U_INTEGRACION,U_ANO_CREACION,U_PROCEDENCIA,CreateDate,UpdateDate,ItemWarehouseInfoCollection'
            ];

            $response = $this->sapService->get("/Items('{$itemCode}')", $params);
            $item = new ArticleResource($response['response']);

            return ApiResponse::success(
                $item,
                'Artículo obtenido exitosamente'
            );
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());
            Log::error("Error al obtener artículo {$itemCode}: " . $formattedException->message);

            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $statusCode === 404 ? 'Artículo no encontrado' : 'Error al obtener el artículo';

            return ApiResponse::error(
                $message,
                [$formattedException->message],
                $statusCode
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/articulos/crear",
     *     summary="Crear artículo en SAP desde FMMS",
     *     tags={"Artículos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "ItemCode", "ItemName", "ItemType", "ItmsGrpCode", "UgpEntry",
     *                 "InvntItem", "SellItem", "PrchseItem", "ManageStockByWarehouse",
     *                 "SWW", "BuyUnitMsr", "SalUnitMsr",
     *                 "U_NEGOCIO", "U_DEPARTAMENTO", "U_LINEA", "U_CLASE", "U_SERIE",
     *                 "U_CONTINUIDAD", "U_TEMPORADA", "U_MARCA", "U_COMPO",
     *                 "U_INTEGRACION", "U_ANO_CREACION",
     *                 "Inventory"
     *             },
     *             @OA\Property(
     *                 property="ItemCode",
     *                 type="string",
     *                 maxLength=50,
     *                 example="ART12345",
     *                 description="Código único del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ItemName",
     *                 type="string",
     *                 maxLength=200,
     *                 example="Pantalón Mujer Tiro Alto",
     *                 description="Nombre del artículo (máx. 200 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ItemType",
     *                 type="string",
     *                 enum={"I","L","T","F"},
     *                 example="I",
     *                 description="Tipo de artículo: I (Items), L (Labor), T (Travel), F (FixedAssets)."
     *             ),
     *             @OA\Property(
     *                 property="ItmsGrpCode",
     *                 type="integer",
     *                 example=101,
     *                 description="Código del grupo de artículos."
     *             ),
     *             @OA\Property(
     *                 property="UgpEntry",
     *                 type="integer",
     *                 example=-1,
     *                 description="Grupo de unidad de medida."
     *             ),
     *             @OA\Property(
     *                 property="InvntItem",
     *                 type="string",
     *                 enum={"tYES","tNO"},
     *                 example="tYES",
     *                 description="Indica si el artículo es inventariable (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="SellItem",
     *                 type="string",
     *                 enum={"tYES","tNO"},
     *                 example="tYES",
     *                 description="Indica si el artículo se vende (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="PrchseItem",
     *                 type="string",
     *                 enum={"tYES","tNO"},
     *                 example="tYES",
     *                 description="Indica si el artículo se compra (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="ManageStockByWarehouse",
     *                 type="string",
     *                 enum={"tYES","tNO"},
     *                 example="tYES",
     *                 description="Indica si maneja stock por bodega (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="SWW",
     *                 type="string",
     *                 maxLength=16,
     *                 example="SKU98765",
     *                 description="Código SKU (máx. 16 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="BuyUnitMsr",
     *                 type="string",
     *                 maxLength=100,
     *                 example="UN",
     *                 description="Unidad de medida para compras (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="SalUnitMsr",
     *                 type="string",
     *                 maxLength=100,
     *                 example="UN",
     *                 description="Unidad de medida para ventas (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="PurPackUn",
     *                 type="integer",
     *                 example="1",
     *                 description="Cantidad de items por packing."
     *             ),
     *             @OA\Property(
     *                 property="U_NEGOCIO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Retail",
     *                 description="Negocio al que pertenece el artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_DEPARTAMENTO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Damas",
     *                 description="Departamento del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_LINEA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Ropa Casual",
     *                 description="Línea o tipo de artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_CLASE",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Jeans",
     *                 description="Clase del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_SERIE",
     *                 type="string",
     *                 maxLength=20,
     *                 example="SER2025",
     *                 description="Serie (máx. 20 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_CONTINUIDAD",
     *                 type="string",
     *                 enum={"S","N"},
     *                 example="S",
     *                 description="Indica si el artículo es de continuidad (S o N)."
     *             ),
     *             @OA\Property(
     *                 property="U_TEMPORADA",
     *                 type="string",
     *                 maxLength=5,
     *                 example="VER25",
     *                 description="Temporada y año del artículo (máx. 5 caracteres, ej: VER25)."
     *             ),
     *             @OA\Property(
     *                 property="U_MARCA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Levis",
     *                 description="Marca del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_COMPO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="100% Algodón",
     *                 description="Composición o material del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_INTEGRACION",
     *                 type="string",
     *                 enum={"S","N"},
     *                 example="S",
     *                 description="Origen del dato: S (Integración) o N (Manual)."
     *             ),
     *             @OA\Property(
     *                 property="U_ANO_CREACION",
     *                 type="integer",
     *                 example=2025,
     *                 description="Año de creación del artículo (4 dígitos)."
     *             ),
     *             @OA\Property(
     *                 property="U_PROCEDENCIA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Importado",
     *                 description="Procedencia del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ORIGEN_PETICION",
     *                 type="string",
     *                 maxLength=15,
     *                 example="FMMS",
     *                 description="Origen de donde se realiza la petición FMMS, WMS, ETC."
     *             ),
     *             @OA\Property(
     *                 property="Inventory",
     *                 type="array",
     *                 minItems=1,
     *                 description="Listado de inventario por almacén.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"WhsCode", "MinStock", "MaxStock"},
     *                     @OA\Property(
     *                         property="WhsCode",
     *                         type="string",
     *                         maxLength=8,
     *                         example="01",
     *                         description="Código del almacén (máx. 8 caracteres)."
     *                     ),
     *                     @OA\Property(
     *                         property="MinStock",
     *                         type="number",
     *                         minimum=0,
     *                         example=10,
     *                         description="Stock mínimo permitido (mín. 0)."
     *                     ),
     *                     @OA\Property(
     *                         property="MaxStock",
     *                         type="number",
     *                         example=100,
     *                         description="Stock máximo permitido (debe ser mayor al mínimo)."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artículo creado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Artículo creado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"ItemCode": "ART12345", "DocEntry": 123}
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación o creación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al crear el artículo"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"El artículo ya existe"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     )
     * )
     */
    public function store(ArticleStoreRequest $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'articles',
            [
                'service_name' => 'Crear',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al enviar carga masiva de artículos: {$integrationLog['message']}",
                [],
                500
            );
        }

        try {
            $data = $this->articleService->prepareItemData($request->validated());
            $wmsData = $this->articleService->prepareWmsItemData($request->validated());

            IntegrationLogger::update('articles', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "INTEGRACIÓN FMMS",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
                'includes_wms_integration' => true,

            ]);

            // Guardar en SAP
            $response = $this->sapService->post('/Items', $data);

            // Si no falla envio a SAP, enviar a WMS
            $wmsResponse = $this->wmsService->makeRequest('POST', 'auth/create_item', $wmsData);

            IntegrationLogger::update('articles', $integrationLog['data']->id, [
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
                'ItemCode' => $data['ItemCode'],
            ], 'Artículo creado exitosamente', 201);
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear artículo: ' . $formattedException->message, [
                'request_data' => $request->validated(),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            // Usar el manejador de errores mejorado
            \App\Services\SapErrorHandlerService::setRequestContext($request->all());
            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            IntegrationLogger::update('articles', $integrationLog['data']->id, [
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
                    'field' => $errorDetails['technical_details']['field'] ?? null,
                    'value' => $errorDetails['technical_details']['value'] ?? null,
                    // Agregar mensaje original para debugging
                    'original_error' => config('app.debug') ? $formattedException->message : null
                ],
                $errorDetails['status_code']
            );
        }
    }
    /**
     * @OA\Patch(
     *     path="/articulos/{itemCode}",
     *     summary="Actualizar artículo existente en SAP",
     *     tags={"Artículos"},
     *     @OA\Parameter(
     *         name="itemCode",
     *         in="path",
     *         required=true,
     *         description="Código único del artículo a actualizar (máx. 50 caracteres).",
     *         @OA\Schema(type="string", example="ART12345")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="ItemName",
     *                 type="string",
     *                 maxLength=200,
     *                 example="Pantalón Mujer Actualizado",
     *                 description="Nombre del artículo (máx. 200 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ItemType",
     *                 type="string",
     *                 enum={"I", "L", "T", "F"},
     *                 example="I",
     *                 description="Tipo de artículo: I (Items), L (Labor), T (Travel), F (FixedAssets)."
     *             ),
     *             @OA\Property(
     *                 property="ItmsGrpCode",
     *                 type="integer",
     *                 example=101,
     *                 description="Código del grupo de artículos."
     *             ),
     *             @OA\Property(
     *                 property="UgpEntry",
     *                 type="integer",
     *                 example=-1,
     *                 description="Código del grupo de unidad de medida."
     *             ),
     *             @OA\Property(
     *                 property="InvntItem",
     *                 type="string",
     *                 enum={"tYES", "tNO"},
     *                 example="tYES",
     *                 description="Indica si el artículo es inventariable (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="SellItem",
     *                 type="string",
     *                 enum={"tYES", "tNO"},
     *                 example="tYES",
     *                 description="Indica si el artículo se vende (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="PrchseItem",
     *                 type="string",
     *                 enum={"tYES", "tNO"},
     *                 example="tYES",
     *                 description="Indica si el artículo se compra (tYES o tNO)."
     *             ),
     *             @OA\Property(
     *                 property="SWW",
     *                 type="string",
     *                 maxLength=16,
     *                 example="SKU98765",
     *                 description="Código SKU (máx. 16 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="BuyUnitMsr",
     *                 type="string",
     *                 maxLength=100,
     *                 example="UN",
     *                 description="Unidad de medida para compras (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="SalUnitMsr",
     *                 type="string",
     *                 maxLength=100,
     *                 example="UN",
     *                 description="Unidad de medida para ventas (máx. 100 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="PurPackUn",
     *                 type="integer",
     *                 example="1",
     *                 description="Cantidad de items por packing."
     *             ),
     *             @OA\Property(
     *                 property="U_NEGOCIO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Retail",
     *                 description="Negocio asociado al artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_DEPARTAMENTO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Damas",
     *                 description="Departamento del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_LINEA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Ropa Casual",
     *                 description="Línea o tipo de artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_CLASE",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Jeans",
     *                 description="Clase del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_SERIE",
     *                 type="string",
     *                 maxLength=20,
     *                 example="SER2026",
     *                 description="Serie del artículo (máx. 20 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_CONTINUIDAD",
     *                 type="string",
     *                 enum={"S", "N"},
     *                 example="S",
     *                 description="Indica si el artículo es de continuidad (S o N)."
     *             ),
     *             @OA\Property(
     *                 property="U_TEMPORADA",
     *                 type="string",
     *                 maxLength=5,
     *                 example="OTO26",
     *                 description="Temporada del artículo (máx. 5 caracteres, ej: OTO26)."
     *             ),
     *             @OA\Property(
     *                 property="U_MARCA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Levis",
     *                 description="Marca del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_COMPO",
     *                 type="string",
     *                 maxLength=50,
     *                 example="70% algodón, 30% poliéster",
     *                 description="Composición del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="U_INTEGRACION",
     *                 type="string",
     *                 enum={"S","N"},
     *                 example="S",
     *                 description="Origen del dato: S (Integración) o N (Manual)."
     *             ),
     *             @OA\Property(
     *                 property="U_ANO_CREACION",
     *                 type="integer",
     *                 example=2025,
     *                 description="Año de creación del artículo (4 dígitos)."
     *             ),
     *             @OA\Property(
     *                 property="U_PROCEDENCIA",
     *                 type="string",
     *                 maxLength=50,
     *                 example="Importado",
     *                 description="Procedencia del artículo (máx. 50 caracteres)."
     *             ),
     *             @OA\Property(
     *                 property="ManageStockByWarehouse",
     *                 type="string",
     *                 enum={"tYES", "tNO"},
     *                 example="tYES",
     *                 description="Indica si maneja stock por bodega (tYES o tNO)."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Artículo actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Artículo actualizado exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "ItemCode": "ART12345",
     *                     "updated_fields": {"ItemName", "U_COMPO", "U_INTEGRACION"}
     *                 }
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Artículo no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Artículo no encontrado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-18T22:47:48-04:00")
     *         )
     *     )
     * )
     */
    public function update(string $itemCode, Request $request): JsonResponse
    {
        $integrationLog = IntegrationLogger::create(
            'articles',
            [
                'service_name' => 'Actualizar',
                'destiny' => 'SAP',
                'status_integration_id' => 1,
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al actualizar artículo: {$integrationLog['message']}",
                [],
                500
            );
        }
        // Inyectar el itemCode de la ruta al request, para que pase validación
        $request->merge(['ItemCode' => $itemCode]);

        $validateRequest = new ArticleUpdateRequest();

        $rules = $validateRequest->rules();
        $messages = method_exists($validateRequest, 'messages') ? $validateRequest->messages() : [];

        $data = Validator::make($request->all(), $rules, $messages);

        if ($data->fails()) {
            IntegrationLogger::update('articles', $integrationLog['data']->id, [
                'code' => 422,
                'message' => 'Error al actualizar artículo',
                'response' => json_encode($data->errors()->toArray()),
                'status_integration_id' => 4,
            ]);

            return ApiResponse::error(
                'Error de validaación.',
                $data->errors()->toArray(),
                422
            );
        }

        try {
            // unset($data['ItemCode']);
            $validatedData = $data->validated();
            $data = $this->articleService->prepareItemDataForUpdate($validatedData);
            $wmsData = $this->articleService->prepareWmsItemData($validatedData);

            IntegrationLogger::update('articles', $integrationLog['data']->id, [
                'origin' => $request->ORIGEN_PETICION ?? "INTEGRACIÓN FMMS",
                'create_body' => json_encode($data),
                'attempts' => 1,
                'status_integration_id' => 2,
                'includes_wms_integration' => true,
            ]);

            // Escapar el ItemCode para seguridad
            $escapedItemCode = htmlspecialchars($itemCode, ENT_QUOTES);
            $response = $this->sapService->patch("/Items('{$escapedItemCode}')", $data);

            // Si no falla envio a SAP, enviar a WMS
            $wmsResponse = $this->wmsService->makeRequest('POST', 'auth/create_item', $wmsData);

            IntegrationLogger::update('articles', $integrationLog['data']->id, [
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
                'ItemCode' => $itemCode,
                'updated_fields' => array_keys($data)
            ], 'Artículo actualizado exitosamente');
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al actualizar artículo: ' . $formattedException->message, [
                'item_code' => $itemCode,
                'request_data' => $data,
                'user_id' => auth()->id()
            ]);

            \App\Services\SapErrorHandlerService::setRequestContext($request->all());
            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            IntegrationLogger::update('articles', $integrationLog['data']->id, [
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
    /**
     * @OA\Post(
     *     path="/articulos/carga-masiva",
     *     summary="Cargar múltiples artículos en SAP desde FMMS",
     *     tags={"Artículos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="Items",
     *                 type="array",
     *                 minItems=1,
     *                 description="Listado de artículos a crear.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={
     *                         "ItemCode", "ItemName", "ItemType", "ItmsGrpCode", "UgpEntry",
     *                         "InvntItem", "SellItem", "PrchseItem", "ManageStockByWarehouse",
     *                         "SWW", "BuyUnitMsr", "SalUnitMsr",
     *                         "U_NEGOCIO", "U_DEPARTAMENTO", "U_LINEA", "U_CLASE", "U_SERIE",
     *                         "U_CONTINUIDAD", "U_TEMPORADA", "U_MARCA", "U_COMPO",
     *                         "U_ANO_CREACION",
     *                         "Inventory"
     *                     },
     *                     @OA\Property(property="ItemCode", type="string", maxLength=50, example="ART12345", description="Código único del artículo."),
     *                     @OA\Property(property="ItemName", type="string", maxLength=200, example="Pantalón Mujer Tiro Alto", description="Nombre del artículo (máx. 200 caracteres)."),
     *                     @OA\Property(property="ItemType", type="string", enum={"I","L","T","F"}, example="I", description="Tipo de artículo: I (Items), L (Labor), T (Travel), F (FixedAssets)."),
     *                     @OA\Property(property="ItmsGrpCode", type="integer", example=101, description="Código del grupo de artículos."),
     *                     @OA\Property(property="UgpEntry", type="integer", example=-1, description="Grupo de unidad de medida."),
     *                     @OA\Property(property="InvntItem", type="string", enum={"tYES","tNO"}, example="tYES", description="Indica si es inventariable."),
     *                     @OA\Property(property="SellItem", type="string", enum={"tYES","tNO"}, example="tYES", description="Indica si el artículo se vende."),
     *                     @OA\Property(property="PrchseItem", type="string", enum={"tYES","tNO"}, example="tYES", description="Indica si el artículo se compra."),
     *                     @OA\Property(property="SWW", type="string", maxLength=16, example="SKU98765", description="Código SKU (máx. 16 caracteres)."),
     *                     @OA\Property(property="BuyUnitMsr", type="string", maxLength=100, example="UN", description="Unidad de medida de compra (máx. 100 caracteres)."),
     *                     @OA\Property(property="SalUnitMsr", type="string", maxLength=100, example="UN", description="Unidad de medida de venta (máx. 100 caracteres)."),
     *                     @OA\Property(property="PurPackUn", type="integer", example="1"),
     *                     @OA\Property(property="U_NEGOCIO", type="string", maxLength=50, example="Retail", description="Negocio al que pertenece."),
     *                     @OA\Property(property="U_DEPARTAMENTO", type="string", maxLength=50, example="Damas", description="Departamento."),
     *                     @OA\Property(property="U_LINEA", type="string", maxLength=50, example="Ropa Casual", description="Línea o tipo de artículo."),
     *                     @OA\Property(property="U_CLASE", type="string", maxLength=50, example="Jeans", description="Clase del artículo."),
     *                     @OA\Property(property="U_SERIE", type="string", maxLength=20, example="SER2025", description="Serie del artículo."),
     *                     @OA\Property(property="U_CONTINUIDAD", type="string", enum={"S","N"}, example="S", description="Si es de continuidad."),
     *                     @OA\Property(property="U_TEMPORADA", type="string", maxLength=5, example="VER25", description="Temporada y año."),
     *                     @OA\Property(property="U_MARCA", type="string", maxLength=50, example="Levis", description="Marca del artículo."),
     *                     @OA\Property(property="U_COMPO", type="string", maxLength=50, example="100% Algodón", description="Composición del artículo."),
     *                     @OA\Property(property="U_INTEGRACION",type="string",enum={"S","N"},example="S",description="Origen del dato: S (Integración) o N (Manual)."),
     *                     @OA\Property(property="U_ANO_CREACION", type="integer", example=2025, description="Año de creación."),
     *                     @OA\Property(property="U_PROCEDENCIA", type="string", maxLength=50, example="Importado", description="Procedencia del artículo."),
     *                     @OA\Property(property="ManageStockByWarehouse", type="string", enum={"tYES","tNO"}, example="tYES", description="Indica si maneja stock por bodega."),
     *                     @OA\Property(
     *                         property="Inventory",
     *                         type="array",
     *                         minItems=1,
     *                         description="Stock por almacén.",
     *                         @OA\Items(
     *                             type="object",
     *                             required={"WhsCode", "MinStock", "MaxStock"},
     *                             @OA\Property(property="WhsCode", type="string", maxLength=8, example="01", description="Código de almacén."),
     *                             @OA\Property(property="MinStock", type="number", example=10, description="Stock mínimo."),
     *                             @OA\Property(property="MaxStock", type="number", example=100, description="Stock máximo.")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Artículos creados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Artículos cargados exitosamente."),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object", example={"ItemCode": "ART12345", "DocEntry": 123})),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-30T14:00:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de uno o más artículos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Se encontraron errores en algunos artículos."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="object", example={"item": "ART001", "error": "El campo ItemName es obligatorio"})
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-30T14:01:00-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al procesar la carga masiva"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"[SAP AG][LIBODBCHDB SO][HDBODBC] General error;260 invalid column name: T0.U_U_PROCEDENCIA (OITM)"}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-30T14:01:00-04:00")
     *         )
     *     )
     * )
     */

    public function sendBatches(ArticleSendBatchesRequest $request): JsonResponse
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $data = $request->validated();

        $integrationLog = IntegrationLogger::create(
            'articles',
            [
                'service_name' => 'Carga Masiva',
                'origin' => $request->ORIGEN_PETICION ?? "INTEGRACIÓN FMMS",
                'destiny' => 'SAP',
                'status_integration_id' => 1
            ]
        );

        if (!$integrationLog['result']) {
            return ApiResponse::error(
                "Error al enviar carga masiva de artículos: {$integrationLog['message']}"
            );
        }

        // Instanciar servicio artículos
        $create = $this->articleService->storeArticle($data['Items'], $integrationLog['data']->id);


        if ($create['success']) {
            return ApiResponse::success($create['data'] ?? [], $create['message'] ?? 'Carga masiva de artúclos finalizada exitosamente', 200);
        }

        return ApiResponse::error($create['message'] ?? 'Error al enviar carga masiva de productos', $create['data'] ?? [], 400, false);
    }




    /**
     * @OA\Get(
     *     path="/articulos/grupos",
     *     summary="Obtener grupos de artículos disponibles",
     *     tags={"Artículos"},
     *     @OA\Response(
     *         response=200,
     *         description="Grupos de artículos obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Grupos de artículos obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="Number", type="integer", example=100),
     *                     @OA\Property(property="GroupName", type="string", example="Artículos")
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-26T12:26:06-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener los grupos de artículos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener los grupos de artículos"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string", example="Connection error")
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-26T12:26:06-04:00")
     *         )
     *     )
     * )
     */
    public function getItemGroups(): JsonResponse
    {
        try {
            $params = [
                '$select' => 'Number,GroupName',
                '$orderby' => 'Number'
            ];

            $response = $this->sapService->get('/ItemGroups', $params);

            return ApiResponse::success(
                $response['response']['value'] ?? $response,
                'Grupos de artículos obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener grupos de artículos: ' . $formattedException->message);

            return ApiResponse::error(
                'Error al obtener los grupos de artículos',
                [$formattedException->message],
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/articulos/almacenes",
     *     summary="Obtener almacenes disponibles",
     *     tags={"Artículos"},
     *     @OA\Response(
     *         response=200,
     *         description="Almacenes obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Almacenes obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="WarehouseCode", type="string", example="001"),
     *                     @OA\Property(property="WarehouseName", type="string", example="TEST")
     *                 )
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-26T12:26:22-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener los almacenes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al obtener los almacenes"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string", example="Connection error")
     *             ),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-26T12:26:22-04:00")
     *         )
     *     )
     * )
     */
    public function getWarehouses(): JsonResponse
    {
        try {
            $params = [
                '$select' => 'WarehouseCode,WarehouseName',
                '$orderby' => 'WarehouseCode'
            ];

            $response = $this->sapService->get('/Warehouses', $params);

            return ApiResponse::success(
                $response['response']['value'] ?? $response,
                'Almacenes obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al obtener almacenes: ' . $formattedException->message);

            return ApiResponse::error(
                'Error al obtener los almacenes',
                [$formattedException->message],
                500
            );
        }
    }
}
