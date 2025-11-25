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
