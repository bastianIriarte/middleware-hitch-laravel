<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\ErrorReportRequest;
use App\Models\Company;
use App\Models\FileType;
use App\Services\FileLogService;
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

            Log::info('Llamando a logReceivedFile', [
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                'user_id' => $userId,
            ]);

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

            if ($request->has('records_count')) {
                $fileLog->update(['records_count' => $request->records_count]);
                Log::info('Actualizado records_count', ['records_count' => $request->records_count]);
            }

            if ($request->has('rejected_count')) {
                $fileLog->update(['rejected_count' => $request->rejected_count]);
                Log::info('Actualizado rejected_count', ['rejected_count' => $request->rejected_count]);
            }

            Log::info('Llamando a processAndUploadFile', ['file_log_id' => $fileLog->id]);

            $uploadResult = $this->fileLogService->processAndUploadFile($fileLog);

            Log::info('Resultado de processAndUploadFile', [
                'success' => $uploadResult['success'],
                'message' => $uploadResult['message'] ?? null,
                'file_log_status' => $fileLog->fresh()->status ?? null,
            ]);

            if ($uploadResult['success']) {
                Log::info('=== UPLOAD EXITOSO ===', [
                    'file_log_id' => $fileLog->id,
                    'status' => $fileLog->status,
                ]);
                
                return ApiResponse::successWithTotal(
                    [
                        'file_log_id' => $fileLog->id,
                        'status' => $fileLog->status,
                        'message' => $uploadResult['message'],
                    ],
                    1,
                    'Archivo recibido y procesado correctamente',
                    200
                );
            } else {
                Log::error('=== UPLOAD FALLIDO ===', [
                    'file_log_id' => $fileLog->id,
                    'status' => $fileLog->status,
                    'message' => $uploadResult['message'],
                ]);
                
                return ApiResponse::errorWithStatus(
                    $uploadResult['message'],
                    [
                        'file_log_id' => $fileLog->id,
                        'status' => $fileLog->status,
                    ],
                    500
                );
            }
        } catch (\Exception $e) {
            Log::error('=== ERROR EN UPLOADFILE ===', [
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
