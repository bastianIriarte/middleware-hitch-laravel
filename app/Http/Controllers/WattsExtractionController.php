<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWattsExtraction;
use App\Services\WattsApiService;
use App\Models\Company;
use App\Models\FileType;
use App\Models\FileLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WattsExtractionController extends Controller
{
    protected $wattsApiService;

    public function __construct(WattsApiService $wattsApiService)
    {
        $this->middleware('auth');
        $this->wattsApiService = $wattsApiService;
    }

    /**
     * Muestra la interfaz de extracciones
     */
    public function index()
    {
        $sidenav = "watts_extraction";

        // Obtener configuración por defecto
        $defaultConfig = $this->wattsApiService->getDefaultConfig();

        // Verificar estado del API de Watts
        $apiHealth = $this->wattsApiService->healthCheck();

        return view('watts-extraction.index', compact('sidenav', 'defaultConfig', 'apiHealth'));
    }

    /**
     * Ejecuta una extracción específica y la encola
     */
    public function execute(Request $request)
    {
        $request->validate([
            'extraction_type' => 'required|in:all,customers,products,vendors,sellout',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            $extractionType = $request->extraction_type;

            // Preparar configuración
            $config = [];
            if ($request->start_date) {
                $config['startDate'] = $request->start_date;
            }
            if ($request->end_date) {
                $config['endDate'] = $request->end_date;
            }

            // Mapeo de tipos de extracción a códigos de FileType
            $fileTypeMap = [
                'customers' => 'CUSTOMERS',
                'products' => 'PRODUCTS',
                'vendors' => 'VENDORS',
                'sellout' => 'SELLOUT',
            ];

            // Obtener Company y FileType
            $company = Company::where('code', 'WATTS')->first();
            $fileTypeCode = $fileTypeMap[$extractionType] ?? 'CUSTOMERS';
            $fileType = FileType::where('code', $fileTypeCode)->first();

            if (!$company || !$fileType) {
                throw new \Exception('No se encontró la configuración de empresa o tipo de archivo');
            }

            // Crear el log inmediatamente con estado 'received'
            $fileLog = FileLog::create([
                'company_id' => $company->id,
                'file_type_id' => $fileType->id,
                // 'original_filename' => "extraction_{$fileType->name}_" . now()->format('YmdHis') . ".csv",
                'original_filename' => "",
                'status' => 'received',
                'received_at' => null,
                'user_created' => auth()->id(),
            ]);

            // Encolar el job para ejecutar después de la respuesta HTTP
            // Pasamos el ID del log para que el job lo actualice
            ProcessWattsExtraction::dispatch($extractionType, $config, $fileLog->id)->afterResponse();

            Log::info("[WattsExtractionController] Job encolado con log creado", [
                'type' => $extractionType,
                'config' => $config,
                'file_log_id' => $fileLog->id,
                'user_id' => auth()->id(),
            ]);

            // Obtener nombre legible del tipo de extracción
            $extractionNames = [
                'all' => 'Extracción Completa (Todos)',
                'customers' => 'Clientes',
                'products' => 'Productos',
                'vendors' => 'Vendedores',
                'sellout' => 'Sell Out',
            ];

            $extractionName = $extractionNames[$extractionType] ?? $extractionType;

            return redirect()
                ->route('watts-extraction.index')
                ->with('success', "Extracción de {$extractionName} encolada correctamente. Se procesará en segundo plano.");
        } catch (\Exception $e) {
            Log::error("[WattsExtractionController] Error al encolar job", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error al encolar la extracción: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Ejecuta todas las extracciones en secuencia
     */
    public function executeAll(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            // Preparar configuración
            $config = [];
            if ($request->start_date) {
                $config['startDate'] = $request->start_date;
            }
            if ($request->end_date) {
                $config['endDate'] = $request->end_date;
            }

            // Mapeo de tipos de extracción a códigos de FileType
            $fileTypeMap = [
                'customers' => 'CUSTOMERS',
                'products' => 'PRODUCTS',
                'vendors' => 'VENDORS',
                'sellout' => 'SELLOUT',
            ];

            // Obtener Company
            $company = Company::where('code', 'WATTS')->first();
            if (!$company) {
                throw new \Exception('No se encontró la configuración de empresa WATTS');
            }

            // Encolar cada tipo de extracción para ejecutar después de la respuesta HTTP
            $types = ['customers', 'products', 'vendors', 'sellout'];
            $fileLogIds = [];

            foreach ($types as $type) {
                // Obtener FileType
                $fileTypeCode = $fileTypeMap[$type] ?? 'CUSTOMERS';
                $fileType = FileType::where('code', $fileTypeCode)->first();

                if (!$fileType) {
                    Log::warning("[WattsExtractionController] No se encontró FileType para {$type}");
                    continue;
                }

                // Crear el log inmediatamente con estado 'received'
                $fileLog = FileLog::create([
                    'company_id' => $company->id,
                    'file_type_id' => $fileType->id,
                    'original_filename' => "extraction_{$type}_" . now()->format('Y-m-d_His') . ".txt",
                    'status' => 'received',
                    'received_at' => now(),
                    'user_created' => auth()->id(),
                ]);

                $fileLogIds[$type] = $fileLog->id;

                // Encolar el job en una cola específica para este tipo
                // Esto permite que cada tipo se reintente independientemente sin bloquear a los demás
                $queueName = "watts_extraction_{$type}";
                
                ProcessWattsExtraction::dispatch($type, $config, $fileLog->id)
                    ->onQueue($queueName)
                    ->afterResponse();
                
                Log::info("[WattsExtractionController] Job encolado en cola específica", [
                    'type' => $type,
                    'queue' => $queueName,
                    'file_log_id' => $fileLog->id,
                ]);
            }

            Log::info("[WattsExtractionController] Todas las extracciones encoladas con logs creados", [
                'types' => $types,
                'config' => $config,
                'file_log_ids' => $fileLogIds,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('watts-extraction.index')
                ->with('success', 'Todas las extracciones han sido encoladas correctamente. Se procesarán en segundo plano.');
        } catch (\Exception $e) {
            Log::error("[WattsExtractionController] Error al encolar todas las extracciones", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error al encolar las extracciones: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verifica el estado del API de Watts
     */
    public function checkHealth()
    {
        $result = $this->wattsApiService->healthCheck();

        return response()->json($result);
    }
}
