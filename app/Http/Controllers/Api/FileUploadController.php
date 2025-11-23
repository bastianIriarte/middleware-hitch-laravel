<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\ErrorReportRequest;
use App\Models\Company;
use App\Models\FileType;
use App\Services\FileLogService;
use App\Jobs\ProcessFileUpload;
use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    protected $fileLogService;

    public function __construct(FileLogService $fileLogService)
    {
        $this->fileLogService = $fileLogService;
    }

    public function uploadFile(FileUploadRequest $request, $company_code, $file_type_code)
    {
        try {
            Log::info('=== INICIO PROCESO UPLOAD ===', [
                'company_code' => $company_code,
                'file_type_code' => $file_type_code,
                'has_file' => $request->hasFile('file'),
                'file_name' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : null,
            ]);

            // Validar que existe la empresa y está activa
            $company = Company::where('code', $company_code)
                ->where('status', true)
                ->first();

            Log::info('Búsqueda de empresa', [
                'company_code' => $company_code,
                'found' => $company ? true : false,
                'company_id' => $company ? $company->id : null,
                'company_name' => $company ? $company->name : null,
            ]);

            if (!$company) {
                Log::warning('Empresa no encontrada o inactiva', ['company_code' => $company_code]);
                return ApiResponse::errorWithStatus(
                    'La empresa no está activa',
                    null,
                    400
                );
            }

            // Validar que existe el tipo de archivo y está activo
            $fileType = FileType::where('code', $file_type_code)
                ->where('status', true)
                ->first();

            Log::info('Búsqueda de tipo de archivo', [
                'file_type_code' => $file_type_code,
                'found' => $fileType ? true : false,
                'file_type_id' => $fileType ? $fileType->id : null,
                'file_type_name' => $fileType ? $fileType->name : null,
            ]);

            if (!$fileType) {
                Log::warning('Tipo de archivo no encontrado o inactivo', ['file_type_code' => $file_type_code]);
                return ApiResponse::errorWithStatus(
                    'El tipo de archivo no está activo',
                    null,
                    400
                );
            }

            // Obtener usuario autenticado (puede ser null si es API token)
            $user = auth()->user();
            $userId = $user ? $user->id : null;

            Log::info('Usuario autenticado', [
                'user_id' => $userId,
                'username' => $user ? $user->username : null,
            ]);

            if (empty($request->file('file'))) {
                Log::error('No se encontró archivo en el request');
                throw new Exception("No se encontró archivo");
            }

            Log::info('Guardando archivo recibido', [
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                'user_id' => $userId,
            ]);

            // Guardar el archivo inmediatamente
            $logResult = $this->fileLogService->logReceivedFile(
                $company,
                $fileType,
                $request->file('file'),
                $userId
            );

            Log::info('Resultado de logReceivedFile', [
                'success' => $logResult['success'],
                'message' => $logResult['message'] ?? null,
                'file_log_id' => $logResult['file_log']->id ?? null,
            ]);

            if (!$logResult['success']) {
                Log::error('Error en logReceivedFile', ['result' => $logResult]);
                return ApiResponse::errorWithStatus(
                    $logResult['message'],
                    null,
                    500
                );
            }

            $fileLog = $logResult['file_log'];

            // Actualizar contadores si vienen en el request
            if ($request->has('records_count')) {
                $fileLog->update(['records_count' => $request->records_count]);
                Log::info('Actualizado records_count', ['records_count' => $request->records_count]);
            }

            if ($request->has('rejected_count')) {
                $fileLog->update(['rejected_count' => $request->rejected_count]);
                Log::info('Actualizado rejected_count', ['rejected_count' => $request->rejected_count]);
            }

            // Encolar el job para procesar el archivo después de la respuesta HTTP
            // Similar a WattsExtractionController, el archivo se procesará en segundo plano
            ProcessFileUpload::dispatch($fileLog->id)->afterResponse();

            Log::info('Job de procesamiento encolado', [
                'file_log_id' => $fileLog->id,
                'job' => 'ProcessFileUpload',
            ]);

            // Retornar respuesta inmediata al watts-api Worker
            // El archivo ya está guardado con status 'received' y se procesará en background
            Log::info('=== ARCHIVO RECIBIDO Y ENCOLADO PARA PROCESAMIENTO ===', [
                'file_log_id' => $fileLog->id,
                'status' => $fileLog->status,
                'company' => $company->name,
                'file_type' => $fileType->name,
            ]);

            return ApiResponse::successWithTotal(
                [
                    'file_log_id' => $fileLog->id,
                    'status' => $fileLog->status,
                    'message' => 'Archivo recibido y encolado para procesamiento',
                    'company' => $company->name,
                    'file_type' => $fileType->name,
                ],
                1,
                'Archivo recibido correctamente. Se procesará en segundo plano.',
                200
            );
        } catch (\Exception $e) {
            Log::error('=== ERROR CRÍTICO EN UPLOADFILE ===', [
                'company_code' => $company_code,
                'file_type_code' => $file_type_code,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::errorWithStatus(
                'Error al procesar el archivo: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    public function reportErrors(ErrorReportRequest $request)
    {
        try {
            $company = Company::where('code', $request->company_code)
                ->where('status', true)
                ->first();

            if (!$company) {
                return ApiResponse::errorWithStatus(
                    'La empresa no está activa',
                    null,
                    400
                );
            }

            $fileType = FileType::where('code', $request->file_type_code)
                ->where('status', true)
                ->first();

            if (!$fileType) {
                return ApiResponse::errorWithStatus(
                    'El tipo de archivo no está activo',
                    null,
                    400
                );
            }

            $user = auth()->user();
            $userId = $user ? $user->id : null;

            $errorsLogged = [];
            $errorsFailed = [];

            foreach ($request->errors as $error) {
                $result = $this->fileLogService->logError(
                    $company,
                    $fileType,
                    $error,
                    $request->file_log_id ?? null,
                    $userId
                );

                if ($result['success']) {
                    $errorsLogged[] = $result['file_error'];
                } else {
                    $errorsFailed[] = $error;
                }
            }

            if (count($errorsFailed) > 0) {
                return ApiResponse::errorWithStatus(
                    'Algunos errores no pudieron ser registrados',
                    [
                        'errors_logged' => count($errorsLogged),
                        'errors_failed' => count($errorsFailed),
                        'failed_errors' => $errorsFailed,
                    ],
                    500
                );
            }

            return ApiResponse::successWithTotal(
                [
                    'errors_logged' => count($errorsLogged),
                ],
                count($errorsLogged),
                'Errores registrados correctamente',
                200
            );
        } catch (\Exception $e) {
            Log::error('Error en reportErrors', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::errorWithStatus(
                'Error al reportar errores: ' . $e->getMessage(),
                null,
                500
            );
        }
    }
}
