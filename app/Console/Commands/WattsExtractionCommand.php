<?php

namespace App\Console\Commands;

use App\Jobs\ProcessWattsExtraction;
use App\Models\Company;
use App\Models\FileType;
use App\Models\FileLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WattsExtractionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watts:extract 
                            {--type=all : Tipo de extracción: all, customers, products, vendors, sellout}
                            {--start-date= : Fecha de inicio (para sellout)}
                            {--end-date= : Fecha de fin (para sellout)}
                            {--async : Ejecutar en cola en lugar de síncronamente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta extracciones de datos de Watts (diarias o bajo demanda)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Iniciando Extracción de Datos Watts ===');
        $this->newLine();

        $type = $this->option('type');
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');
        $async = $this->option('async');

        // Validar que la empresa WATTS exista
        $company = Company::where('code', 'WATTS')->first();
        if (!$company) {
            $this->error('❌ No se encontró la empresa WATTS en la base de datos');
            Log::error('[WattsExtractionCommand] Empresa WATTS no encontrada');
            return 1;
        }

        if (!$company->status) {
            $this->error('❌ La empresa WATTS está inactiva');
            Log::error('[WattsExtractionCommand] Empresa WATTS inactiva');
            return 1;
        }

        $this->info("✓ Empresa: {$company->name} ({$company->code})");

        // Validar configuración FTP
        if (!$company->ftpConfig || !$company->ftpConfig->status) {
            $this->error('❌ No hay configuración FTP activa para WATTS');
            Log::error('[WattsExtractionCommand] Sin configuración FTP');
            return 1;
        }

        $this->info('✓ Configuración FTP encontrada');
        $this->newLine();

        // Preparar configuración
        $config = [];
        if ($startDate) {
            $config['startDate'] = $startDate;
            $this->info("📅 Fecha inicio: {$startDate}");
        }
        if ($endDate) {
            $config['endDate'] = $endDate;
            $this->info("📅 Fecha fin: {$endDate}");
        }

        // Determinar qué extracciones ejecutar
        $types = $type === 'all' 
            ? ['customers', 'products', 'vendors', 'sellout'] 
            : [$type];

        $this->info("🔄 Tipo de extracción: " . ($type === 'all' ? 'TODAS' : strtoupper($type)));
        $this->info("⚙️  Modo: " . ($async ? 'ASÍNCRONO (cola)' : 'SÍNCRONO'));
        $this->newLine();

        // Mapeo de tipos a códigos de FileType
        $fileTypeMap = [
            'customers' => 'CUSTOMERS',
            'products' => 'PRODUCTS',
            'vendors' => 'VENDORS',
            'sellout' => 'SELLOUT',
        ];

        $successCount = 0;
        $failedCount = 0;

        foreach ($types as $extractionType) {
            $this->info("📦 Procesando: " . strtoupper($extractionType));

            // Obtener FileType
            $fileTypeCode = $fileTypeMap[$extractionType] ?? 'CUSTOMERS';
            $fileType = FileType::where('code', $fileTypeCode)->first();

            if (!$fileType || !$fileType->status) {
                $this->warn("  ⚠️  FileType {$fileTypeCode} no encontrado o inactivo - OMITIDO");
                $failedCount++;
                continue;
            }

            // Crear FileLog
            $fileLog = FileLog::create([
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                'original_filename' => "extraction_{$extractionType}_" . now()->format('Y-m-d_His') . ".txt",
                'status' => 'received',
                'received_at' => now(),
                'user_created' => null, // Ejecutado por comando
            ]);

            $this->info("  ✓ FileLog creado (ID: {$fileLog->id})");

            // Encolar o ejecutar síncronamente
            if ($async) {
                $queueName = "watts_extraction_{$extractionType}";
                ProcessWattsExtraction::dispatch($extractionType, $config, $fileLog->id)
                    ->onQueue($queueName);
                
                $this->info("  ✓ Job encolado en: {$queueName}");
            } else {
                $this->info("  ⏳ Ejecutando síncronamente...");
                try {
                    ProcessWattsExtraction::dispatchSync($extractionType, $config, $fileLog->id);
                    $this->info("  ✅ Completado exitosamente");
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("  ❌ Error: " . $e->getMessage());
                    $failedCount++;
                }
            }

            $this->newLine();
        }

        // Resumen
        $this->info('=== Resumen de Ejecución ===');
        if ($async) {
            $this->info('✓ ' . count($types) . ' extracciones encoladas correctamente');
            $this->info('💡 Los jobs se procesarán en segundo plano');
        } else {
            $this->info("✅ Exitosas: {$successCount}");
            if ($failedCount > 0) {
                $this->error("❌ Fallidas: {$failedCount}");
            }
        }

        Log::info('[WattsExtractionCommand] Comando ejecutado', [
            'type' => $type,
            'async' => $async,
            'config' => $config,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);

        return $failedCount > 0 ? 1 : 0;
    }
}
