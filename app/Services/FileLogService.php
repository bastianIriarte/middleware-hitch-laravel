<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FileType;
use App\Models\FileLog;
use App\Models\FileError;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileLogService
{
    protected $ftpService;

    public function __construct(FtpService $ftpService)
    {
        $this->ftpService = $ftpService;
    }

    public function logReceivedFile(Company $company, FileType $fileType, UploadedFile $file, $userId = null)
    {
        try {
            Log::info('--- FileLogService::logReceivedFile INICIO ---', [
                'company_id' => $company->id,
                'company_code' => $company->code,
                'file_type_id' => $fileType->id,
                'file_type_code' => $fileType->code,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'user_id' => $userId,
            ]);

            $storedFileName = $this->generateStoredFileName($company, $fileType, $file);
            
            Log::info('Nombre de archivo generado', [
                'stored_filename' => $storedFileName,
            ]);

            $storagePath = "uploads/{$company->code}/{$fileType->code}";
            Log::info('Guardando archivo en storage', [
                'storage_path' => $storagePath,
                'stored_filename' => $storedFileName,
            ]);

            $filePath = $file->storeAs(
                $storagePath,
                $storedFileName,
                'local'
            );

            Log::info('Archivo guardado en storage', [
                'file_path' => $filePath,
                'full_path' => storage_path('app/' . $filePath),
                'exists' => file_exists(storage_path('app/' . $filePath)),
            ]);

            Log::info('Creando registro en file_logs', [
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFileName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'status' => 'received',
            ]);

            $fileLog = FileLog::create([
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFileName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'status' => 'received',
                'received_at' => now(),
                'user_created' => $userId,
            ]);

            Log::info('--- FileLogService::logReceivedFile EXITOSO ---', [
                'file_log_id' => $fileLog->id,
                'company' => $company->code,
                'file_type' => $fileType->code,
                'filename' => $file->getClientOriginalName(),
                'stored_as' => $storedFileName,
            ]);

            return [
                'success' => true,
                'message' => 'Archivo recibido correctamente',
                'file_log' => $fileLog,
            ];
        } catch (\Exception $e) {
            Log::error('--- FileLogService::logReceivedFile ERROR ---', [
                'company' => $company->code,
                'file_type' => $fileType->code,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al registrar archivo: ' . $e->getMessage(),
            ];
        }
    }

    public function processAndUploadFile(FileLog $fileLog)
    {
        Log::info('--- FileLogService::processAndUploadFile INICIO ---', [
            'file_log_id' => $fileLog->id,
            'current_status' => $fileLog->status,
            'file_path' => $fileLog->file_path,
        ]);

        DB::beginTransaction();
        try {
            Log::info('Actualizando estado a processing', ['file_log_id' => $fileLog->id]);
            $fileLog->update(['status' => 'processing']);

            $localFilePath = storage_path('app/' . $fileLog->file_path);
            
            Log::info('Verificando archivo local', [
                'local_file_path' => $localFilePath,
                'exists' => file_exists($localFilePath),
                'size' => file_exists($localFilePath) ? filesize($localFilePath) : null,
            ]);

            if (!file_exists($localFilePath)) {
                throw new \Exception('Archivo local no encontrado: ' . $localFilePath);
            }

            $company = $fileLog->company;
            $fileType = $fileLog->fileType;

            Log::info('Datos para upload FTP', [
                'company_id' => $company->id,
                'company_code' => $company->code,
                'file_type_id' => $fileType->id,
                'file_type_code' => $fileType->code,
            ]);

            $remoteFileName = $fileLog->stored_filename;
            $remotePath = "{$fileType->code}/{$remoteFileName}";

            Log::info('Llamando a FTP upload', [
                'local_file' => $localFilePath,
                'remote_path' => $remotePath,
                'company' => $company->code,
            ]);

            $uploadResult = $this->ftpService->uploadFile($company, $localFilePath, $remotePath);

            Log::info('Resultado de FTP upload', [
                'success' => $uploadResult['success'],
                'message' => $uploadResult['message'] ?? null,
            ]);

            if ($uploadResult['success']) {
                Log::info('Actualizando estado a uploaded', ['file_log_id' => $fileLog->id]);
                
                $fileLog->update([
                    'status' => 'uploaded',
                    'uploaded_at' => now(),
                    'ftp_response' => $uploadResult['message'],
                ]);

                DB::commit();

                Log::info('--- FileLogService::processAndUploadFile EXITOSO ---', [
                    'file_log_id' => $fileLog->id,
                    'company' => $company->code,
                    'file_type' => $fileType->code,
                    'status' => 'uploaded',
                ]);

                return [
                    'success' => true,
                    'message' => 'Archivo procesado y subido al FTP exitosamente',
                    'file_log' => $fileLog->fresh(),
                ];
            } else {
                Log::warning('FTP upload falló, actualizando estado a failed', [
                    'file_log_id' => $fileLog->id,
                    'error' => $uploadResult['message'],
                ]);

                $fileLog->update([
                    'status' => 'failed',
                    'error_message' => $uploadResult['message'],
                ]);

                DB::commit();

                return [
                    'success' => false,
                    'message' => $uploadResult['message'],
                    'file_log' => $fileLog->fresh(),
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('--- FileLogService::processAndUploadFile ERROR ---', [
                'file_log_id' => $fileLog->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $fileLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar archivo: ' . $e->getMessage(),
            ];
        }
    }

    public function logError(Company $company, FileType $fileType, array $errorData, $fileLogId = null, $userId = null)
    {
        try {
            $fileError = FileError::create([
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                'file_log_id' => $fileLogId,
                'error_type' => $errorData['error_type'] ?? 'general',
                'error_message' => $errorData['error_message'],
                'error_details' => $errorData['error_details'] ?? null,
                'line_number' => $errorData['line_number'] ?? null,
                'record_data' => $errorData['record_data'] ?? null,
                'severity' => $errorData['severity'] ?? 'medium',
                'user_created' => $userId,
            ]);

            Log::warning("Error registrado", [
                'file_error_id' => $fileError->id,
                'company' => $company->code,
                'file_type' => $fileType->code,
                'error_message' => $errorData['error_message'],
            ]);

            return [
                'success' => true,
                'message' => 'Error registrado correctamente',
                'file_error' => $fileError,
            ];
        } catch (\Exception $e) {
            Log::error("Error al registrar error de archivo", [
                'company' => $company->code,
                'file_type' => $fileType->code,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al registrar error: ' . $e->getMessage(),
            ];
        }
    }

    public function getLogsByCompanyAndType($companyId = null, $fileTypeId = null, $filters = [])
    {
        $query = FileLog::with(['company', 'fileType', 'errors']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($fileTypeId) {
            $query->where('file_type_id', $fileTypeId);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $query->orderBy('created_at', 'desc');

        if (isset($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    public function getErrorsByCompanyAndType($companyId = null, $fileTypeId = null, $filters = [])
    {
        $query = FileError::with(['company', 'fileType', 'fileLog']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($fileTypeId) {
            $query->where('file_type_id', $fileTypeId);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $query->orderBy('created_at', 'desc');

        if (isset($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    protected function generateStoredFileName(Company $company, FileType $fileType, UploadedFile $file)
    {
        $timestamp = now()->format('YmdHis');
        $extension = $file->getClientOriginalExtension();
        return "{$company->code}_{$fileType->code}_{$timestamp}.{$extension}";
    }

    public function getStatsByCompanyAndType($companyId = null, $fileTypeId = null)
    {
        $query = FileLog::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($fileTypeId) {
            $query->where('file_type_id', $fileTypeId);
        }

        $stats = [
            'total' => $query->count(),
            'received' => (clone $query)->where('status', 'received')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'uploaded' => (clone $query)->where('status', 'uploaded')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'total_errors' => FileError::when($companyId, function($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                })
                ->when($fileTypeId, function($q) use ($fileTypeId) {
                    return $q->where('file_type_id', $fileTypeId);
                })
                ->count(),
        ];

        return $stats;
    }
}
