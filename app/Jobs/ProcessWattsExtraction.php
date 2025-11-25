<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\FileType;
use App\Models\FileLog;
use App\Models\FileError;
use App\Models\FtpConfig;
use App\Services\WattsApiService;
use App\Services\FtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWattsExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutos
    public $tries = 3;

    protected $extractionType;
    protected $config;
    protected $companyCode;
    protected $fileLogId;

    /**
     * Create a new job instance.
     *
     * @param string $extractionType Tipo de extracción: 'all', 'customers', 'products', 'vendors', 'sellout'
     * @param array $config Configuración adicional (fechas, etc.)
     * @param int|null $fileLogId ID del log a actualizar
     * @param string|null $companyCode Código de la empresa (ej: 'WATTS')
     */
    public function __construct(string $extractionType, array $config = [], ?int $fileLogId = null, ?string $companyCode = 'WATTS')
    {
        $this->extractionType = $extractionType;
        $this->config = $config;
        $this->fileLogId = $fileLogId;
        $this->companyCode = $companyCode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WattsApiService $wattsApiService, FtpService $ftpService)
    {
        $fileLog = null;

        try {
            // Obtener el log si existe
            if ($this->fileLogId) {
                $fileLog = FileLog::find($this->fileLogId);
            }

            Log::info("[ProcessWattsExtraction] Iniciando job de extracción", [
                'type' => $this->extractionType,
                'company' => $this->companyCode,
                'config' => $this->config,
                'file_log_id' => $this->fileLogId,
            ]);

            // Actualizar estado a "processing"
            if ($fileLog) {
                $fileLog->update([
                    'status' => 'processing',
                    'received_at' => now(),
                ]);
            }

            // Obtener configuración por defecto si no se proporciona
            if (empty($this->config)) {
                $this->config = $wattsApiService->getDefaultConfig();
            }

            // Agregar customerCode y fileCode al config usando los datos del Job
            $this->config['customerCode'] = $this->companyCode;
            $this->config['fileCode'] = $this->getFileCodeFromType($this->extractionType);

            // Ejecutar la extracción según el tipo
            $result = $this->executeExtraction($wattsApiService);

            if ($result['success']) {
                Log::info("[ProcessWattsExtraction] Extracción completada exitosamente", [
                    'type' => $this->extractionType,
                    'message' => $result['message'],
                    'has_file_content' => isset($result['fileContent']),
                ]);

                // Si el API devolvió el contenido del archivo, subirlo al FTP
                if (isset($result['fileContent']) && $fileLog) {
                    Log::info("[ProcessWattsExtraction] Subiendo archivo al FTP", [
                        'filename' => $result['filename'],
                        'file_size' => strlen($result['fileContent']),
                    ]);

                    // Obtener configuración FTP de la empresa
                    $company = Company::where('code', $this->companyCode)->first();
                    if (!$company) {
                        throw new \Exception("Empresa {$this->companyCode} no encontrada");
                    }

                    $ftpConfig = FtpConfig::where('company_id', $company->id)->first();
                    if (!$ftpConfig) {
                        throw new \Exception("Configuración FTP no encontrada para empresa {$this->companyCode}");
                    }

                    // Subir archivo al FTP
                    $filename = $result['filename'] ?? "extraction_{$this->extractionType}_" . now()->format('Y-m-d_His') . ".txt";

                    // Generar nombre de archivo almacenado con timestamp único
                    $storedFilename = now()->format('Ymd_His') . '_' . $filename;

                    // Guardar el archivo de forma permanente
                    $storagePath = 'watts/' . $this->companyCode . '/' . date('Y/m');
                    $fullStoragePath = storage_path('app/' . $storagePath);

                    if (!file_exists($fullStoragePath)) {
                        mkdir($fullStoragePath, 0755, true);
                    }

                    $localFilePath = $fullStoragePath . '/' . $storedFilename;
                    file_put_contents($localFilePath, $result['fileContent']);

                    // Subir al FTP usando el archivo guardado
                    $remotePath = $filename;
                    $uploadResult = $ftpService->uploadFile($company, $localFilePath, $remotePath);

                    if (!$uploadResult['success']) {
                        throw new \Exception("Error al subir archivo al FTP: " . $uploadResult['message']);
                    }

                    Log::info("[ProcessWattsExtraction] Archivo subido exitosamente al FTP", [
                        'filename' => $filename,
                        'remote_path' => $uploadResult['remote_path'] ?? null,
                    ]);
                }

                // Actualizar log como exitoso
                if ($fileLog) {
                    $recordCount = 0;
                    if (isset($result['data']['TotalRecords']) && is_array($result['data']['TotalRecords'])) {
                        $recordCount = array_sum($result['data']['TotalRecords']);
                    }

                    $updateData = [
                        'status' => 'uploaded',
                        'uploaded_at' => now(),
                        'records_count' => $recordCount,
                        'error_message' => null,
                    ];

                    // Si se subió un archivo, actualizar el nombre y tamaño
                    if (isset($result['filename'])) {
                        $updateData['original_filename'] = $result['filename'];
                    }

                    // Guardar el tamaño del archivo en bytes
                    if (isset($result['fileContent'])) {
                        $updateData['file_size'] = strlen($result['fileContent']);

                        Log::info("[ProcessWattsExtraction] Guardando tamaño de archivo", [
                            'file_size' => $updateData['file_size'],
                            'filename' => $updateData['original_filename'] ?? null,
                        ]);
                    }

                    // Guardar información de almacenamiento local si se guardó
                    if (isset($storedFilename) && isset($storagePath)) {
                        $updateData['stored_filename'] = $storedFilename;
                        $updateData['file_path'] = $storagePath . '/' . $storedFilename;
                    }

                    $fileLog->update($updateData);

                    Log::info("[ProcessWattsExtraction] FileLog actualizado", [
                        'file_log_id' => $fileLog->id,
                        'update_data' => $updateData,
                    ]);
                }
            } else {
                Log::error("[ProcessWattsExtraction] Extracción falló", [
                    'type' => $this->extractionType,
                    'message' => $result['message'],
                    'status' => $result['status'],
                ]);

                // Actualizar log como fallido
                if ($fileLog) {
                    $fileLog->update([
                        'status' => 'failed',
                        'error_message' => $result['message'],
                    ]);

                    // Crear registro de error
                    $this->createErrorLog($fileLog, $result['message']);
                }

                // Lanzar excepción para reintentar (hasta 3 veces)
                throw new \Exception($result['message']);
            }
        } catch (\Exception $e) {
            Log::error("[ProcessWattsExtraction] Error en job de extracción", [
                'type' => $this->extractionType,
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
     * Obtiene el código de archivo (fileCode) según el tipo de extracción
     */
    protected function getFileCodeFromType(string $extractionType): string
    {
        $fileCodeMap = [
            'customers' => 'CUSTOMERS',
            'products' => 'PRODUCTS',
            'vendors' => 'VENDORS',
            'sellout' => 'SELLOUT',
            'all' => 'ALL',
        ];

        return $fileCodeMap[$extractionType] ?? 'UNKNOWN';
    }

    /**
     * Ejecuta la extracción según el tipo
     */
    protected function executeExtraction(WattsApiService $wattsApiService): array
    {
        Log::info(["[executeExtraction]" => $wattsApiService]);
        switch ($this->extractionType) {
            case 'all':
                return $wattsApiService->extractAll($this->config);

            case 'customers':
                return $wattsApiService->extractCustomers($this->config);

            case 'products':
                return $wattsApiService->extractProducts($this->config);

            case 'vendors':
                return $wattsApiService->extractVendors($this->config);

            case 'sellout':
                return $wattsApiService->extractSellOut($this->config);

            default:
                return [
                    'success' => false,
                    'message' => "Tipo de extracción no válido: {$this->extractionType}",
                    'data' => null,
                    'status' => 400,
                ];
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
                'error_type' => 'extraction_failed',
                'error_message' => $errorMessage,
                'error_details' => "Extracción de tipo '{$this->extractionType}' falló",
                'severity' => 'high',
                'user_created' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            Log::error("[ProcessWattsExtraction] Error al crear registro de error", [
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
        Log::error("[ProcessWattsExtraction] Job falló después de todos los reintentos", [
            'type' => $this->extractionType,
            'company' => $this->companyCode,
            'error' => $exception->getMessage(),
            'file_log_id' => $this->fileLogId,
        ]);

        // Actualizar log como fallido permanentemente
        if ($this->fileLogId) {
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
                    'error_details' => "Job falló después de {$this->tries} intentos",
                    'severity' => 'critical',
                ]);
            }
        }
    }
}
