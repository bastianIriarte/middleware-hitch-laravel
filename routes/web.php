<?php

use App\Http\Controllers\ApiConnectionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MyProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SapIntegrationsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

#PAGINA DE INICIO
Route::get('/', function () {
    return redirect()->route('dashboard');
});

#*********************************************#
#**************** ROUTES AUTH ****************#
#*********************************************#
#LOGIN ADMINISTRADOR
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'login_validate'])->name('login-post');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

#REGISTRO
# Route::get('/registro', [RegisterController::class, 'register'])->name('register');
# Route::post('/registro', [RegisterController::class, 'register_validate'])->name('register-post');
Route::get('/generar-usuario', [RegisterController::class, 'su_register'])->name('generate-root');
Route::get('/sesion-finalizada', [LoginController::class, 'session_finish'])->name('session-finish');

#DASHBOARD
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

#*********************************************#
#*************** ROUTES USERS ****************#
#*********************************************#

Route::get('/dashboard/usuarios', [UsersController::class, 'index'])->name('users');
Route::post('/dashboard/usuarios/nuevo', [UsersController::class, 'store'])->name('user-store');
Route::post('/dashboard/usuarios/{id}/editar', [UsersController::class, 'update'])->name('user-update');
Route::post('/dashboard/usuarios/eliminar', [UsersController::class, 'destroy'])->name('user-delete');
Route::post('/dashboard/usuarios/restablecer-contrasena', [UsersController::class, 'restore_password'])->name('user-restore-password');
Route::post('/dashboard/usuarios/confirmar-cuenta', [UsersController::class, 'confirm_account'])->name('user-confirm-account');

#*********************************************#
#*********** ROUTES INTEGRATIONS *************#
#*********************************************#
Route::get('/dashboard/integraciones/{slug}', [IntegrationsController::class, 'index'])->name('integrations');
// Route::post('/dashboard/integraciones/{slug}/reintegrar', [IntegrationsController::class, 'reintegrate'])->name('integrations-reintegrate');
Route::post('/dashboard/integraciones/{slug}/cerrar', [IntegrationsController::class, 'close'])->name('integrations-close');

#*********************************************#
#********** ROUTES API CONNECTIONS ***********#
#*********************************************#
Route::get('/dashboard/conexiones', [ApiConnectionsController::class, 'index'])->name('api-connections');
Route::post('/dashboard/conexiones/actualizar', [ApiConnectionsController::class, 'update'])->name('api-connections-update');

#*********************************************#
#******** ROUTES FILE MANAGEMENT *************#
#*********************************************#
Route::prefix('dashboard/archivos')->name('file-management.')->group(function () {
    Route::get('/', [App\Http\Controllers\FileManagementController::class, 'index'])->name('index');
    Route::get('/empresas', [App\Http\Controllers\FileManagementController::class, 'companies'])->name('companies');
    Route::get('/empresas/{companyId}/ftp', [App\Http\Controllers\FileManagementController::class, 'ftpConfig'])->name('ftp-config');
    Route::post('/ftp/guardar', [App\Http\Controllers\FileManagementController::class, 'saveFtpConfig'])->name('ftp-save');
    Route::get('/empresas/{companyId}/ftp/test', [App\Http\Controllers\FileManagementController::class, 'testFtpConnection'])->name('test-ftp');
    Route::get('/logs', [App\Http\Controllers\FileManagementController::class, 'logs'])->name('logs');
    Route::get('/errores', [App\Http\Controllers\FileManagementController::class, 'errors'])->name('errors');
    Route::get('/estadisticas', [App\Http\Controllers\FileManagementController::class, 'stats'])->name('stats');
});

Route::get('api/documentation/swagger.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});


Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
});
