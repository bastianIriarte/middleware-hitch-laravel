<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\Sap\ArticlesController;
use App\Http\Controllers\Api\Sap\BillController;
use App\Http\Controllers\Api\Sap\BusinessPartnersController;
use App\Http\Controllers\Api\Sap\DepositsController;
// use App\Http\Controllers\Api\Sap\DepositsController;
use App\Http\Controllers\Api\Sap\GoodsIssuesController;
use App\Http\Controllers\Api\Sap\GoodsReceiptsController;
use App\Http\Controllers\Api\Sap\InventoryMovementsController;
use App\Http\Controllers\Api\Sap\JournalEntriesController;
// use App\Http\Controllers\Api\Sap\JournalEntriesController;
use App\Http\Controllers\Api\Sap\PurchaseInvoicesController;
use App\Http\Controllers\Api\Sap\PurchaseOrdersController;
use App\Http\Controllers\Api\Sap\ReserveInvoicesController;
use App\Http\Controllers\Api\Sap\StockTransferController;
use App\Http\Controllers\Api\Sap\TransferRequestsController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


#*********************************************#
#**************** ROUTES AUTH ****************#
#*********************************************#
Route::post('/login', [LoginController::class, 'login']);
Route::get('/generar-usuario', [RegisterController::class, 'su_register']);

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout']);

    #*********************************************#
    #***************** ROUTES SAP ****************#
    #*********************************************#
    // Rutas de artículos
    Route::prefix('articulos')->group(function () {
        // CRUD básico
        Route::get('/', [ArticlesController::class, 'index']);                    // GET /api/articulos
        Route::get('/buscar', [ArticlesController::class, 'search']);             // GET /api/articulos/buscar?q=term
        Route::get('/grupos', [ArticlesController::class, 'getItemGroups']);
        Route::get('/almacenes', [ArticlesController::class, 'getWarehouses']);
        Route::post('/crear', [ArticlesController::class, 'store']);              // POST /api/articulos/crear
        Route::patch('/{itemCode}', [ArticlesController::class, 'update']);       // PATCH /api/articulos/{ItemCode}
        Route::post('/carga-masiva', [ArticlesController::class, 'sendBatches']);       // PATCH /api/articulos/carga-masiva
        Route::get('/{itemCode}', [ArticlesController::class, 'show']);           // GET /api/articulos/{itemCode}
        Route::get('/{itemCode}/inventario', [ArticlesController::class, 'getInventoryByWarehouses']); // GET /api/articulos/{itemCode}/inventario
    });

    // SOCIOS DE NEGOCIO
    Route::prefix('socios')->group(function () {
        Route::get('/', [BusinessPartnersController::class, 'index']);
        Route::post('/crear', [BusinessPartnersController::class, 'store']);
        Route::patch('/{CardCode}', [BusinessPartnersController::class, 'update']);
        Route::get('/{cardCode}', [BusinessPartnersController::class, 'show']);
    });

    #*********************************************#
    #************* DOCUMENTOS COMPRAS ************#
    #*********************************************#

    // ÓRDENES DE COMPRA
    Route::prefix('ordenes-compra')->group(function () {
        Route::get('/', [PurchaseOrdersController::class, 'index']);
        Route::get('/pendientes-pago', [PurchaseOrdersController::class, 'getPendingAmounts']);
        Route::post('/crear', [PurchaseOrdersController::class, 'store']);
        Route::get('/{docEntry}', [PurchaseOrdersController::class, 'show']);
        Route::get('/{docEntry}/entradas-mercaderia', [PurchaseOrdersController::class, 'getGoodsReceipts']);
    });

    // FACTURAS DE RESERVA
    Route::prefix('facturas-reserva')->group(function () {
        Route::get('/', [PurchaseInvoicesController::class, 'index']);
        Route::get('/{docEntry}', [PurchaseInvoicesController::class, 'show']);
        Route::get('/{docEntry}/entradas-mercaderia', [PurchaseInvoicesController::class, 'getGoodsReceipts']);
        Route::post('/crear', [PurchaseInvoicesController::class, 'store']);
    });

    // DEVOLUCIONES
    // Route::prefix('devoluciones')->group(function () {
    //     Route::get('/', [ReturnsController::class, 'index']);
    //     Route::get('/{docEntry}', [ReturnsController::class, 'show']);
    //     Route::post('/crear', [ReturnsController::class, 'store']);
    // });

    Route::prefix('boletas-pos')->group(function () {
        Route::post('/integrar', [BillController::class, 'index']);
        Route::post('/generar-pago', [BillController::class, 'generatePayment']);
    });

    #*********************************************#
    #********** MOVIMIENTOS INVENTARIO ***********#
    #*********************************************#

    Route::prefix('inventario')->group(function () {
        // ENTRADAS Y SALIDAS
        Route::post('/entradas-mercaderia/crear', [InventoryMovementsController::class, 'createGoodsReceipt']);
        Route::get('/entradas', [InventoryMovementsController::class, 'getInventoryEntries']);
        Route::get('/salidas', [InventoryMovementsController::class, 'getInventoryExits']);
        Route::post('/salidas/crear', [InventoryMovementsController::class, 'createInventoryExit']);

        // TRANSFERENCIAS
        Route::get('/transferencias', [StockTransferController::class, 'getStockTransfers']);
        Route::get('/transferencias/{docEntry}', [StockTransferController::class, 'showStockTransfer']);
        Route::post('/transferencias/crear', [StockTransferController::class, 'createStockTransfer']);
        Route::post('/transferencias/desde-solicitud', [StockTransferController::class, 'createTransferFromRequest']);

        // SOLICITUDES DE TRASLADO
        Route::get('/solicitudes-traslado', [TransferRequestsController::class, 'getTransferRequests']);
        Route::get('/solicitudes-traslado/{docEntry}', [TransferRequestsController::class, 'showTransferRequest']);
        Route::post('/solicitudes-traslado/crear', [TransferRequestsController::class, 'createTransferRequest']);

        // CONTEOS
        Route::get('/conteos', [InventoryMovementsController::class, 'getInventoryCountings']);
    });

    #*********************************************#
    #************** MÓDULOS FMMS *****************#
    #*********************************************#

    // ASIENTOS CONTABLES
    Route::prefix('asientos')->group(function () {
        Route::get('/', [JournalEntriesController::class, 'index']);
        Route::get('/{jdtNum}', [JournalEntriesController::class, 'show']);
        Route::post('/crear', [JournalEntriesController::class, 'store']);
    });

    // DEPÓSITOS
    Route::prefix('depositos')->group(function () {
        Route::get('/', [DepositsController::class, 'index']);
        Route::get('/{depositNumber}', [DepositsController::class, 'show']);
        Route::post('/crear', [DepositsController::class, 'store']);
    });

    #*********************************************#
    #************** CONSULTAS STOCK **************#
    #*********************************************#

    // Route::get('/ventas/por-tienda', [SalesController::class, 'getSalesByStore']);
    // Route::get('/stock/por-tienda', [StockController::class, 'getStockByStore']);

    // Route::post('/socios/crear', [BusinessPartnersController::class, 'store']);
    // Route::patch('/socios/actualizar', [BusinessPartnersController::class, 'update']);

    // Route::post('/ordenes-compra/crear', [PurchaseOrdersController::class, 'store']);
    // Route::get('/ordenes-compra/pendientes-pago', [PurchaseOrdersController::class, 'getPendingAmounts']);

    // Route::post('/facturas-reserva/crear', [ReserveInvoicesController::class, 'store']);

    // Route::post('/solicitudes-transferencias/crear', [StockTransferRequestsController::class, 'store']);
    // Route::get('/solicitudes-transferencias/solicitudes-abiertas', [StockTransferRequestsController::class, 'getOpenStockRequests']);

    // Route::post('/transferencias-stock/crear', [StockTransfersController::class, 'store']);

    // Route::post('/asientos/crear', [JournalEntriesController::class, 'store']);

    // Route::post('/depositos/crear', [DepositsController::class, 'store']);

    // Route::post('/entradas-mercaderia/crear', [GoodsReceiptsController::class, 'store']);

    // Route::post('/salidas-mercaderia/crear', [GoodsIssuesController::class, 'store']);

    // Route::get('/ventas/por-tienda', [SalesController::class, 'getSalesByStore']);

    // Route::get('/stock/por-tienda', [StockController::class, 'getStockByStore']);

    #*********************************************#
    #********* FILE MANAGEMENT SYSTEM ************#
    #*********************************************#

    // FILE UPLOADS
    Route::prefix('files')->group(function () {
        Route::post('/upload/{company_code}/{file_type_code}', [App\Http\Controllers\Api\FileUploadController::class, 'uploadFile']);
        Route::post('/errors/report', [App\Http\Controllers\Api\FileUploadController::class, 'reportErrors']);
    });

    // FILE CONFIGURATION
    Route::prefix('config')->group(function () {
        Route::get('/companies', [App\Http\Controllers\Api\FileConfigController::class, 'getCompanies']);
        Route::get('/file-types', [App\Http\Controllers\Api\FileConfigController::class, 'getFileTypes']);
        Route::get('/ftp/{companyId}', [App\Http\Controllers\Api\FileConfigController::class, 'getFtpConfig']);
        Route::post('/ftp/save', [App\Http\Controllers\Api\FileConfigController::class, 'saveFtpConfig']);
        Route::post('/ftp/test/{companyId}', [App\Http\Controllers\Api\FileConfigController::class, 'testFtpConnection']);
        Route::patch('/companies/{companyId}/status', [App\Http\Controllers\Api\FileConfigController::class, 'updateCompanyStatus']);
        Route::patch('/file-types/{fileTypeId}/status', [App\Http\Controllers\Api\FileConfigController::class, 'updateFileTypeStatus']);
    });

    // FILE LOGS
    Route::prefix('logs')->group(function () {
        Route::get('/files', [App\Http\Controllers\Api\FileLogsController::class, 'getLogs']);
        Route::get('/errors', [App\Http\Controllers\Api\FileLogsController::class, 'getErrors']);
        Route::get('/stats', [App\Http\Controllers\Api\FileLogsController::class, 'getStats']);
    });
});
