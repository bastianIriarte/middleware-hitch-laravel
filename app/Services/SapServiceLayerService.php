<?php

namespace App\Services;

use App\Models\ApiConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SapServiceLayerService
{
    protected $client;
    protected $baseUrl;
    protected $sessionId;
    protected $timeout;
    protected $sapCredentials;

    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }
        // Buscar la conexión configurada para SAP
        $connection = ApiConnection::where('software', 'sap')->where('deleted', 0)->first();

        // if (!$connection || !$connection->endpoint || !$connection->username || !$connection->password) {
        //     Log::error('No se encontraron credenciales válidas para SAP en api_connections.');
        //     throw new \Exception('Credenciales SAP no configuradas correctamente en la tabla api_connections.');
        // }

        $this->baseUrl = $connection->endpoint;
        $this->timeout = config('sap.service_layer.timeout', 30);

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => config('sap.service_layer.verify_ssl', false), // Cambiar a false para SSL auto-firmado
            'cookies' => true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        // Guardar credenciales para uso en authenticate()
        $encryptionService = new EncryptionService();
        $this->sapCredentials = [
            'CompanyDB' => $connection->database,
            'UserName' => $connection->username,
            'Password' => $encryptionService->decrypt($connection->password),
        ];
    }

    /**
     * Autenticar con SAP Service Layer
     */
    public function authenticate(): bool
    {
        try {
            // Verificar si ya tenemos una sesión válida en cache
            $cachedSession = Cache::get('sap_session_id');
            if ($cachedSession) {
                $this->sessionId = $cachedSession;
                // Verificar si la sesión sigue siendo válida
                if ($this->validateSession()) {
                    return true;
                } else {
                    Cache::forget('sap_session_id');
                    $this->sessionId = null;
                }
            }

            $credentials = $this->sapCredentials;

            Log::info('Intentando autenticación SAP', [
                'database' => $credentials['CompanyDB'],
                'username' => $credentials['UserName'],
                'url' => $this->baseUrl
            ]);

            $response = $this->client->post('/b1s/v1/Login', [
                'json' => $credentials
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                $this->sessionId = $data['SessionId'];

                // Guardar en cache por 25 minutos (las sesiones de SAP duran 30 min por defecto)
                Cache::put('sap_session_id', $this->sessionId, now()->addMinutes(25));

                Log::info('Autenticación SAP exitosa', [
                    'session_id' => substr($this->sessionId, 0, 8) . '...'
                ]);
                return true;
            }

            return false;
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';

            Log::error('Error de autenticación SAP', [
                'status_code' => $statusCode,
                'error_message' => $errorMessage,
                'error_body' => $errorBody
            ]);

            throw new \Exception('Error de autenticación con SAP: ' . $errorMessage);
        }
    }

    /**
     * Validar sesión actual
     */
    private function validateSession(): bool
    {
        if (!$this->sessionId) {
            return false;
        }

        try {
            // Hacer una petición simple para validar la sesión
            $response = $this->client->get('/b1s/v1/Items', [
                'headers' => [
                    'Cookie' => "B1SESSION={$this->sessionId}"
                ],
                'query' => [
                    '$top' => 1,
                    '$select' => 'ItemCode'
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener un Guzzle Client preparado para usar en Pool.
     */
    public function executePool(array $jobs, int $concurrency = 5): array
    {
        $results = [];

        // 1) Asegurar autenticación
        if (!$this->sessionId) {
            $this->authenticate();
        } else {
            if (!$this->validateSession()) {
                $this->authenticate();
            }
        }

        $sessionId = $this->sessionId ?? Cache::get('sap_session_id');
        if (!$sessionId) {
            throw new \Exception('No hay sessionId disponible para ejecutar pool.');
        }

        // 2) Preparar requests
        $requests = [];
        $meta = [];

        foreach ($jobs as $job) {
            $itemCode = $job['itemCode'] ?? uniqid('job_');
            $method   = strtoupper($job['method'] ?? 'GET');
            $uri      = $job['uri'] ?? '/';
            $payload  = $job['payload'] ?? null;
            $headers  = $job['headers'] ?? [];

            if (stripos($uri, '/b1s/v1') === false) {
                $requestPath = '/b1s/v1' . (strpos($uri, '/') === 0 ? $uri : "/{$uri}");
            } else {
                $requestPath = $uri;
            }

            $finalHeaders = array_merge([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'Cookie'       => "B1SESSION={$sessionId}"
            ], $headers);

            $body = $payload !== null ? json_encode($payload) : null;

            $requests[$itemCode] = new Request($method, $requestPath, $finalHeaders, $body);

            $meta[$itemCode] = [
                'method' => $method,
                'uri'    => $requestPath,
                'payload'=> $payload,
                'headers'=> $finalHeaders
            ];
        }

        // 3) Ejecutar Pool
        $pool = new Pool($this->client, $requests, [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $itemCode) use (&$results, $meta) {
                $method = $meta[$itemCode]['method'] ?? 'POST';
                $msg = $method === 'POST'
                    ? 'Registro creado exitosamente'
                    : ($method === 'PATCH'
                        ? 'Registro actualizado correctamente'
                        : 'Registro procesado exitosamente');

                $results[$itemCode] = [
                    'success' => true,
                    'message' => $msg,
                    'errors'  => []
                ];
            },
            'rejected' => function ($reason, $itemCode) use (&$results, $meta) {
                $statusCode = null;
                if ($reason instanceof RequestException && $reason->getResponse()) {
                    $statusCode = $reason->getResponse()->getStatusCode();
                }

                if ($statusCode === 401 && isset($meta[$itemCode])) {
                    try {
                        $this->authenticate();
                        $newSession = $this->sessionId ?? Cache::get('sap_session_id');

                        $m = $meta[$itemCode];
                        $headers = array_merge($m['headers'], ['Cookie' => "B1SESSION={$newSession}"]);

                        $resp = $this->client->request($m['method'], $m['uri'], [
                            'headers' => $headers,
                            'json'    => $m['payload'],
                            'timeout' => $this->timeout
                        ]);

                        $msg = $m['method'] === 'POST'
                            ? 'Registro creado exitosamente'
                            : ($m['method'] === 'PATCH'
                                ? 'Registro actualizado correctamente'
                                : 'Registro procesado exitosamente');

                        $results[$itemCode] = [
                            'success' => true,
                            'message' => $msg,
                            'errors'  => []
                        ];
                        return;
                    } catch (\Throwable $e) {
                        $results[$itemCode] = [
                            'success' => false,
                            'message' => 'Error al enviar registro a SAP.',
                            'errors'  => [ $e->getMessage() ]
                        ];
                        return;
                    }
                }

                $msg = $reason instanceof \Throwable ? $reason->getMessage() : 'Error desconocido';
                try {
                    $parsed = \App\Services\SapErrorHandlerService::parseError(
                        $msg,
                        method_exists($reason, 'getCode') ? $reason->getCode() : null
                    );
                    $userMsg = $parsed['user_message'] ?? $msg;
                } catch (\Throwable $ex) {
                    $userMsg = $msg;
                }

                $results[$itemCode] = [
                    'success' => false,
                    'message' => 'Error al enviar registro a SAP',
                    'errors'  => [ $userMsg ]
                ];
            }
        ]);

        $pool->promise()->wait();

        // 4) Asegurar todos los jobs con resultado
        foreach ($jobs as $job) {
            $ic = $job['itemCode'] ?? null;
            if ($ic && !isset($results[$ic])) {
                $results[$ic] = [
                    'success' => false,
                    'message' => 'No se obtuvo respuesta del pool',
                    'errors'  => []
                ];
            }
        }

        return $results;
    }

    /**
     * Cerrar sesión
     */
    public function logout(): bool
    {
        try {
            if (!$this->sessionId) {
                return true;
            }

            $this->client->post('/b1s/v1/Logout', [
                'headers' => [
                    'Cookie' => "B1SESSION={$this->sessionId}"
                ]
            ]);

            Cache::forget('sap_session_id');
            $this->sessionId = null;

            Log::info('Sesión SAP cerrada');
            return true;
        } catch (RequestException $e) {
            Log::error('Error al cerrar sesión SAP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Realizar petición GET
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->makeRequest('GET', $endpoint, null, $params);
    }

    /**
     * Realizar petición POST
     */
    public function post(string $endpoint, array $data): array
    {
        return $this->makeRequest('POST', $endpoint, $data);
    }

    /**
     * Realizar petición PATCH
     */
    public function patch(string $endpoint, array $data): array
    {
        return $this->makeRequest('PATCH', $endpoint, $data);
    }

    /**
     * Realizar petición DELETE
     */
    public function delete(string $endpoint): array
    {
        return $this->makeRequest('DELETE', $endpoint);
    }

    /**
     * Realizar petición HTTP genérica
     */
    protected function makeRequest(string $method, string $endpoint, array $data = null, array $params = []): array
    {
        $sapRequest = [];

        try {
            // Asegurar autenticación
            if (!$this->sessionId) {
                $this->authenticate();
            }

            $options = [
                'headers' => [
                    'Cookie' => "B1SESSION={$this->sessionId}",
                    'Prefer' => 'return=representation'
                ]
            ];

            // Agregar datos para POST/PATCH
            if ($data !== null) {
                $options['json'] = $data;
            }

            // Agregar parámetros de query para GET
            if (!empty($params)) {
                $options['query'] = $params;
            }

            // Construir URL completa
            $url = '/b1s/v1' . $endpoint;

            $sapRequest = [
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'params' => $params
            ];

            Log::info("SAP Request: {$method} {$url}", [
                'data' => $data,
                'params' => $params
            ]);

            $response = $this->client->request($method, $url, $options);

            $responseData = [
                'request' => $sapRequest,
                'response' => json_decode($response->getBody(), true)
            ];

            Log::info("SAP Response: {$response->getStatusCode()}", [
                'response' => $responseData ? array_keys($responseData) : 'empty',
                'response2' => $response,
            ]);

            return $responseData ?? [];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';

            Log::error("SAP Request Error: {$method} {$endpoint}", [
                'status_code' => $statusCode,
                'error' => $errorBody,
                'data' => $data,
                'params' => $params
            ]);

            // Si es error 401, intentar reautenticar una sola vez
            if ($statusCode === 401 && !isset($this->retryAttempted)) {
                $this->retryAttempted = true;
                Cache::forget('sap_session_id');
                $this->sessionId = null;

                // Intentar una vez más después de reautenticar
                $result = $this->makeRequest($method, $endpoint, $data, $params);
                unset($this->retryAttempted);
                return $result;
            }

            // Intentar decodificar el error de SAP
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message']['value'] ?? $e->getMessage();

            throw new \Exception(json_encode([
                'message' => $errorMessage,
                'status' => $statusCode,
                'request' => $sapRequest,
                'response_error' => $errorBody,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), $statusCode);
        } finally {
            Cache::forget('sap_session_id');
            $this->sessionId = null;
        }
    }

    /**
     * Enviar raw body a SAP con headers personalizados (para $batch)
     */
    public function rawPost(string $endpoint, string $body, array $headers = []): string
    {
        try {
            // Asegurar autenticación
            if (!$this->sessionId) {
                $this->authenticate();
            }

            // Merge headers con sesión activa
            $finalHeaders = array_merge([
                'Cookie' => "B1SESSION={$this->sessionId}",
                'Prefer' => 'odata.maxpagesize=100'
            ], $headers);

            $url = '/b1s/v1' . $endpoint;

            Log::info("SAP Raw POST: {$url}", [
                'headers' => $finalHeaders,
                'body_snippet' => substr($body, 0, 500)
            ]);

            $response = $this->client->request('POST', $url, [
                'headers' => $finalHeaders,
                'body'    => $body
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorBody  = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';

            Log::error("SAP Raw POST Error: {$endpoint}", [
                'status_code'  => $statusCode,
                'error'        => $errorBody,
                'body_snippet' => substr($body, 0, 500)
            ]);

            // Si es error 401, intentar reautenticar una sola vez (igual que makeRequest)
            if ($statusCode === 401 && !isset($this->retryAttempted)) {
                $this->retryAttempted = true;
                Cache::forget('sap_session_id');
                $this->sessionId = null;

                Log::warning('Sesión SAP expirada. Reautenticando y reintentando rawPost...', [
                    'endpoint' => $endpoint
                ]);

                $result = $this->rawPost($endpoint, $body, $headers);
                unset($this->retryAttempted);
                return $result;
            }

            throw new \Exception("SAP batch error: {$errorBody}", $statusCode);
        }
    }

    /**
     * Obtener información de la sesión actual
     */
    public function getSessionInfo(): ?string
    {
        return $this->sessionId;
    }

    /**
     * Verificar si la conexión está activa
     */
    public function isConnected(): bool
    {
        try {
            // Usar un endpoint válido para verificar conexión
            $this->get('/Items', ['$top' => 1, '$select' => 'ItemCode']);
            return true;
        } catch (\Exception $e) {
            Log::info('Verificación de conexión falló: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener metadatos de una entidad
     */
    public function getMetadata(string $entity): array
    {
        try {
            $response = $this->client->get("/b1s/v1/\$metadata#{$entity}", [
                'headers' => [
                    'Cookie' => "B1SESSION={$this->sessionId}"
                ]
            ]);

            return ['metadata' => $response->getBody()->getContents()];
        } catch (RequestException $e) {
            throw new \Exception('Error al obtener metadatos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener información básica del sistema
     */
    public function getSystemInfo(): array
    {
        try {
            // Obtener lista de entidades disponibles
            return $this->get('/');
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener información del sistema: ' . $e->getMessage());
        }
    }

    /**
     * Probar conectividad básica sin autenticación
     */
    public function testConnectivity(): bool
    {
        try {
            $response = $this->client->get('/b1s/v1/');
            return $response->getStatusCode() === 401; // 401 es esperado sin autenticación
        } catch (\Exception $e) {
            Log::error('Test de conectividad falló: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatear excepciones
     */
    public static function SapFormattedException(string $exception)
    {
        $exceptionMessage = $exception;
        $requestData = null;
        $json = json_decode($exceptionMessage, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($json['message'])) {
            $exceptionMessage = $json['message'];
            $requestData = $json['request'] ?? '';
        }

        return (object)[
            'request' => $requestData,
            'message' => $exceptionMessage
        ];
    }

    /**
     * Probar conexión a SAP usando parámetros entregados
     */
    public function testConnection(string $endpoint, string $database, string $username, string $password): array
    {
        try {
            $client = new Client([
                'base_uri' => rtrim($endpoint, '/'),
                'timeout'  => config('sap.service_layer.timeout', 30),
                'verify'   => config('sap.service_layer.verify_ssl', false),
                'cookies'  => true,
                'headers'  => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ]
            ]);

            $credentials = [
                'CompanyDB' => $database,
                'UserName'  => $username,
                'Password'  => $password,
            ];

            // 1. Intentar autenticación
            $response = $client->post('/b1s/v1/Login', ['json' => $credentials]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                $sessionId = $data['SessionId'] ?? null;

                if ($sessionId) {
                    // 2. Validar sesión con un GET mínimo
                    $validate = $client->get('/b1s/v1/Items', [
                        'headers' => [
                            'Cookie' => "B1SESSION={$sessionId}"
                        ],
                        'query' => [
                            '$top'    => 1,
                            '$select' => 'ItemCode'
                        ]
                    ]);

                    // 3. Cerrar sesión
                    $client->post('/b1s/v1/Logout', [
                        'headers' => [
                            'Cookie' => "B1SESSION={$sessionId}"
                        ]
                    ]);

                    if ($validate->getStatusCode() === 200) {
                        return [
                            'success'     => true,
                            'status_code' => 200,
                            'message'     => 'Conexión a SAP exitosa',
                            'endpoint'    => $endpoint,
                            'database'    => $database,
                            'username'    => $username,
                        ];
                    }
                }
            }

            return [
                'success'     => false,
                'status_code' => 500,
                'message'     => 'No se pudo autenticar con SAP'
            ];
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            Log::error("Error en testConnection SAP", [
                'status_code' => $statusCode,
                'error'       => $e->getMessage()
            ]);

            return [
                'success'     => false,
                'status_code' => $statusCode,
                'message'     => $e->getMessage()
            ];
        }
    }
}
