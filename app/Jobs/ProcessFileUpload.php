<?php

namespace App\Jobs;

use App\Models\FileLog;
use App\Models\FileError;
use App\Services\FileLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutos
    public $tries = 3;

    protected $fileLogId;

    /**
     * Create a new job instance.
     *
     * @param int $fileLogId ID del FileLog a procesar
     */
    public function __construct(int $fileLogId)
    {
        $this->fileLogId = $fileLogId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FileLogService $fileLogService)
    {
        $fileLog = null;

        try {
            // Obtener el log
            $fileLog = FileLog::find($this->fileLogId);

            if (!$fileLog) {
                throw new \Exception("FileLog con ID {$this->fileLogId} no encontrado");
            }

            Log::info("[ProcessFileUpload] Iniciando procesamiento de archivo", [
                'file_log_id' => $this->fileLogId,
                'company_id' => $fileLog->company_id,
                'file_type_id' => $fileLog->file_type_id,
                'filename' => $fileLog->original_filename,
            ]);

            // Actualizar estado a "processing"
            $fileLog->update([
                'status' => 'processing',
            ]);

            // Procesar y subir el archivo (a FTP/S3)
            $result = $fileLogService->processAndUploadFile($fileLog);

            if ($result['success']) {
                Log::info("[ProcessFileUpload] Archivo procesado y subido exitosamente", [
                    'file_log_id' => $this->fileLogId,
                    'message' => $result['message'],
                    'filename' => $fileLog->original_filename,
                ]);

                // Actualizar log como exitoso
                $fileLog->update([
                    'status' => 'uploaded',
                    'uploaded_at' => now(),
                    'error_message' => null,
                ]);
            } else {
                Log::error("[ProcessFileUpload] Error al procesar archivo", [
                    'file_log_id' => $this->fileLogId,
                    'message' => $result['message'],
                    'filename' => $fileLog->original_filename,
                ]);

                // Actualizar log como fallido
                $fileLog->update([
                    'status' => 'failed',
                    'error_message' => $result['message'],
                ]);

                // Crear registro de error
                $this->createErrorLog($fileLog, $result['message']);

                // Lanzar excepción para reintentar (hasta 3 veces)
                throw new \Exception($result['message']);
            }
        } catch (\Exception $e) {
            Log::error("[ProcessFileUpload] Error crítico en job de procesamiento", [
                'file_log_id' => $this->fileLogId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Actualizar log como fallido
            if ($fileLog) {
                $fileLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                // Crear registro de error
                $this->createErrorLog($fileLog, $e->getMessage());
            }

            // Re-lanzar para que el job se reintente
            throw $e;
        }
    }

    /**
     * Crea un registro de error
     */
    protected function createErrorLog(FileLog $fileLog, string $errorMessage): void
    {
        try {
            FileError::create([
                'company_id' => $fileLog->company_id,
                'file_type_id' => $fileLog->file_type_id,
                'file_log_id' => $fileLog->id,
                'error_type' => 'upload_failed',
                'error_message' => $errorMessage,
                'error_details' => "Procesamiento y upload del archivo '{$fileLog->original_filename}' falló",
                'severity' => 'high',
                'user_created' => null, // El job se ejecuta en background sin usuario
            ]);
        } catch (\Exception $e) {
            Log::error("[ProcessFileUpload] Error al crear registro de error", [
                'file_log_id' => $fileLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("[ProcessFileUpload] Job falló después de todos los reintentos", [
            'file_log_id' => $this->fileLogId,
            'error' => $exception->getMessage(),
            'tries' => $this->tries,
        ]);

        // Actualizar log como fallido permanentemente
        $fileLog = FileLog::find($this->fileLogId);
        if ($fileLog) {
            $fileLog->update([
                'status' => 'failed',
                'error_message' => 'Falló después de ' . $this->tries . ' intentos: ' . $exception->getMessage(),
            ]);

            // Crear registro de error crítico
            FileError::create([
                'company_id' => $fileLog->company_id,
                'file_type_id' => $fileLog->file_type_id,
                'file_log_id' => $fileLog->id,
                'error_type' => 'job_failed',
                'error_message' => $exception->getMessage(),
                'error_details' => "Job ProcessFileUpload falló después de {$this->tries} intentos",
                'severity' => 'critical',
            ]);
        }
    }
}
