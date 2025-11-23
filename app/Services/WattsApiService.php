<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WattsApiService
{
    protected $baseUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = env('WATTS_API_URL', 'http://localhost:5000');
        $this->apiKey = env('WATTS_API_KEY', '');
        $this->timeout = 300; // 5 minutos
    }

    /**
     * Ejecuta la extracción completa de todos los tipos de archivos
     */
    public function extractAll(array $config = [])
    {
        return $this->makeRequest('POST', '/api/watts/extract-all', $config);
    }

    /**
     * Extrae solo clientes
     */
    public function extractCustomers(array $config = [])
    {
        return $this->makeRequest('POST', '/api/watts/extract-customers', $config);
    }

    /**
     * Extrae solo productos
     */
    public function extractProducts(array $config = [])
    {
        return $this->makeRequest('POST', '/api/watts/extract-products', $config);
    }

    /**
     * Extrae solo vendedores
     */
    public function extractVendors(array $config = [])
    {
        return $this->makeRequest('POST', '/api/watts/extract-vendors', $config);
    }

    /**
     * Extrae solo sell out
     */
    public function extractSellOut(array $config = [])
    {
        return $this->makeRequest('POST', '/api/watts/extract-sellout', $config);
    }

    /**
     * Verifica el estado del API de Watts
     */
    public function healthCheck()
    {
        return $this->makeRequest('GET', '/api/watts/health', [], false);
    }

    /**
     * Realiza una petición HTTP al API de Watts
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], bool $requireAuth = true)
    {
        try {
            $url = $this->baseUrl . $endpoint;

            Log::info("[WattsApiService] Enviando request a Watts API", [
                'method' => $method,
                'url' => $url,
                'data' => $data,
            ]);

            $request = Http::timeout($this->timeout);

            // Agregar API Key si es requerido
            if ($requireAuth && !empty($this->apiKey)) {
                $request = $request->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ]);
            }

            // Realizar la petición
            if ($method === 'GET') {
                $response = $request->get($url, $data);
            } else {
                $response = $request->post($url, $data);
            }

            // Verificar si la respuesta fue exitosa
            if ($response->successful()) {
                $body = $response->json();

                // Extraer contenido del archivo si viene en la respuesta
                $fileContent = null;
                $filename = null;

                // Si los datos contienen fileContent y fileName
                if (isset($body['data']['fileContent']) && isset($body['data']['fileName'])) {
                    // Decodificar el contenido base64
                    $compressedData = base64_decode($body['data']['fileContent']);

                    // Descomprimir el archivo GZip
                    $fileContent = gzdecode($compressedData);

                    if ($fileContent === false) {
                        Log::error("[WattsApiService] Error al descomprimir archivo GZip");
                        // Intentar usar el dato sin descomprimir (compatibilidad)
                        $fileContent = $compressedData;
                    }

                    $filename = $body['data']['fileName'];

                    Log::info("[WattsApiService] Archivo descomprimido", [
                        'compressed_size' => strlen($compressedData),
                        'decompressed_size' => strlen($fileContent),
                        'filename' => $filename,
                    ]);
                } elseif (isset($body['fileContent']) && isset($body['filename'])) {
                    // Compatibilidad con formato antiguo (sin comprimir)
                    $fileContent = base64_decode($body['fileContent']);
                    $filename = $body['filename'];
                }

                Log::info("[WattsApiService] Respuesta exitosa de Watts API", [
                    'status' => $response->status(),
                    'success' => $body['success'] ?? null,
                    'message' => $body['message'] ?? null,
                    'has_file_content' => $fileContent !== null,
                    'filename' => $filename,
                ]);

                return [
                    'success' => $body['success'] ?? true,
                    'message' => $body['message'] ?? 'Operación exitosa',
                    'data' => $body['data'] ?? $body,
                    'fileContent' => $fileContent, // Contenido del archivo decodificado
                    'filename' => $filename, // Nombre del archivo
                    'status' => $response->status(),
                ];
            } else {
                $errorBody = $response->json();

                Log::error("[WattsApiService] Error en Watts API", [
                    'status' => $response->status(),
                    'error' => $errorBody,
                ]);

                return [
                    'success' => false,
                    'message' => $errorBody['message'] ?? 'Error en la petición',
                    'data' => $errorBody,
                    'status' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("[WattsApiService] Excepción al llamar Watts API", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'data' => null,
                'status' => 500,
            ];
        }
    }

    /**
     * Obtiene la configuración por defecto para las extracciones
     */
    public function getDefaultConfig(): array
    {
        return [
            'startDate' => now()->subDays(30)->format('Y-m-d'),
            'endDate' => now()->format('Y-m-d'),
            'outputPath' => '/temp/watts',
        ];
    }
}
