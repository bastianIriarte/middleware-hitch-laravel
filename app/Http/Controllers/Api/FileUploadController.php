<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\ErrorReportRequest;
use App\Models\Company;
use App\Models\FileType;
use App\Models\FileLog;
use App\Services\FileLogService;
use App\Jobs\ProcessFileUpload;
use App\Helpers\ApiResponse;
use App\Models\FileError;
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
        Log::info($request->all());
        Log::info('====== reportErrors: INICIO ======', [
            'company_code' => $request->company_code,
            'file_type_code' => $request->file_type_code,
            'errors_count' => count($request->errors ?? []),
            'errors_data' => $request->errors,
        ]);

        try {
            // Buscar empresa SIN validar status - queremos guardar el error de todas formas
            $company = Company::where('code', $request->company_code)->first();
            $configError = null;

            if (!$company) {
                $configError = "Empresa '{$request->company_code}' no encontrada";
                Log::error('reportErrors: Empresa no encontrada', [
                    'company_code' => $request->company_code,
                ]);
            } elseif (!$company->status) {
                $configError = "Empresa '{$request->company_code}' está inactiva";
                Log::warning('reportErrors: Empresa inactiva', [
                    'company_code' => $request->company_code,
                    'company_id' => $company->id,
                ]);
            }

            // Buscar tipo de archivo SIN validar status - queremos guardar el error de todas formas
            $fileType = FileType::where('code', $request->file_type_code)->first();

            if (!$fileType) {
                $configError = ($configError ? $configError . ' y ' : '') . "Tipo de archivo '{$request->file_type_code}' no encontrado";
                Log::error('reportErrors: Tipo de archivo no encontrado', [
                    'file_type_code' => $request->file_type_code,
                ]);
            } elseif (!$fileType->status) {
                $configError = ($configError ? $configError . ' y ' : '') . "Tipo de archivo '{$request->file_type_code}' está inactivo";
                Log::warning('reportErrors: Tipo de archivo inactivo', [
                    'file_type_code' => $request->file_type_code,
                    'file_type_id' => $fileType->id,
                ]);
            }

            $user = auth()->user();
            $userId = $user ? $user->id : null;

            // Crear registro de FileLog con estado 'error' si tenemos company y fileType
            if ($company && $fileType) {
                try {
                    $fileLog = FileLog::create([
                        'company_id' => $company->id,
                        'file_type_id' => $fileType->id,
                        'status' => 'failed',
                        'received_at' => now(),
                        'user_created' => $userId,
                    ]);

                    Log::info('reportErrors: FileLog con error creado', [
                        'file_log_id' => $fileLog->id,
                        'company_id' => $company->id,
                        'file_type_id' => $fileType->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('reportErrors: No se pudo crear FileLog con error', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $errorsLogged = [];
            $errorsFailed = [];

            // Si hay error de configuración, guardar un error especial que lo indique
            if ($configError) {
                try {
                    $configErrorRecord = FileError::create([
                        'company_id' => $company ? $company->id : null,
                        'file_type_id' => $fileType ? $fileType->id : null,
                        'file_log_id' => isset($fileLog->id) ? $fileLog->id : null,
                        'error_type' => 'configuration',
                        'error_message' => $configError,
                        'error_details' => json_encode([
                            'company_code' => $request->company_code,
                            'file_type_code' => $request->file_type_code,
                            'errors_received' => count($request->errors),
                        ]),
                        'line_number' => null,
                        'record_data' => null,
                        'severity' => 'critical',
                        'user_created' => $userId,
                    ]);

                    Log::warning('Error de configuración registrado en BD', [
                        'file_error_id' => $configErrorRecord->id,
                        'config_error' => $configError,
                    ]);
                } catch (\Exception $e) {
                    Log::error('No se pudo guardar error de configuración', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Intentar guardar todos los errores recibidos
            foreach ($request->errors as $error) {
                try {
                    // Transformar formato de entrada (line, error) al formato esperado por logError
                    $errorMessage = is_array($error['error'] ?? null)
                        ? implode('; ', $error['error'])
                        : ($error['error_message'] ?? $error['error'] ?? 'Error sin mensaje');

                    $errorData = [
                        'error_type' => $error['error_type'] ?? 'validation',
                        'error_message' => $errorMessage,
                        'error_details' => json_encode([
                            'line' => $error['line'] ?? null,
                            'original_error' => $error['error'] ?? null,
                        ]),
                        'line_number' => $error['line_number'] ?? null,
                        'record_data' => $error['record_data'] ?? ($error['line'] ?? null),
                        'severity' => $error['severity'] ?? 'medium',
                    ];

                    // Si tenemos company y fileType válidos, usar el servicio
                    if ($company && $fileType) {
                        Log::info('reportErrors: Usando fileLogService', [
                            'company_id' => $company->id,
                            'file_type_id' => $fileType->id,
                            'errorData' => $errorData,
                        ]);

                        $result = $this->fileLogService->logError(
                            $company,
                            $fileType,
                            $errorData,
                            isset($fileLog->id) ? $fileLog->id : null,
                            $userId
                        );

                        if ($result['success']) {
                            $errorsLogged[] = $result['file_error'];
                            Log::info('reportErrors: Error guardado exitosamente', [
                                'file_error_id' => $result['file_error']->id,
                            ]);
                        } else {
                            $errorsFailed[] = $error;
                            Log::warning('reportErrors: Error no pudo ser guardado', [
                                'reason' => $result['message'] ?? 'Unknown',
                            ]);
                        }
                    } else {
                        // Si no tenemos company o fileType, guardar directamente
                        Log::info('reportErrors: Guardando directamente (sin company o fileType)', [
                            'company_id' => $company ? $company->id : null,
                            'file_type_id' => $fileType ? $fileType->id : null,
                            'errorData' => $errorData,
                        ]);

                        $fileError = FileError::create([
                            'company_id' => $company ? $company->id : null,
                            'file_type_id' => $fileType ? $fileType->id : null,
                            'file_log_id' => isset($fileLog->id) ? $fileLog->id : null,
                            'error_type' => $errorData['error_type'],
                            'error_message' => $errorData['error_message'],
                            'error_details' => $errorData['error_details'],
                            'line_number' => $errorData['line_number'],
                            'record_data' => $errorData['record_data'],
                            'severity' => $errorData['severity'],
                            'user_created' => $userId,
                        ]);

                        $errorsLogged[] = $fileError;
                        Log::info('reportErrors: Error guardado exitosamente (directo)', [
                            'file_error_id' => $fileError->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    $errorsFailed[] = $error;
                    Log::error('reportErrors: Excepción al guardar error individual', [
                        'error' => $error,
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // IMPORTANTE: Siempre devolver success para que el worker no falle
            // El objetivo es registrar los errores, no validar que todos se guardaron
            $message = count($errorsFailed) > 0
                ? sprintf('Errores registrados: %d de %d', count($errorsLogged), count($request->errors))
                : 'Todos los errores registrados correctamente';

            return ApiResponse::successWithTotal(
                [
                    'errors_logged' => count($errorsLogged),
                    'errors_failed' => count($errorsFailed),
                    'total_errors' => count($request->errors),
                ],
                count($errorsLogged),
                $message,
                200
            );
        } catch (\Exception $e) {
            // Solo en caso de error crítico, loguear pero intentar devolver respuesta
            Log::error('Error crítico en reportErrors', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // Aún en error crítico, devolver 200 para que el worker no reintente
            return ApiResponse::successWithTotal(
                [
                    'errors_logged' => 0,
                    'errors_failed' => count($request->errors ?? []),
                    'critical_error' => $e->getMessage(),
                ],
                0,
                'Error al procesar errores, pero registrado en logs',
                200
            );
        }
    }
}
