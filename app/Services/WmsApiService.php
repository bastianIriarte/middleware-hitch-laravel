<?php

namespace App\Services;

use App\Models\ApiConnection;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WmsApiService
{
    protected $client;
    protected $baseUrl;

    protected $timeout = 30; // Timeout configurable
    protected $wmsCredentials;

    protected $defaultHeaders;

    protected $wmsToken;

    public $lastRequest;
    public $lastResponse;

    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }
        // Buscar la conexión configurada para WMS
        $connection = ApiConnection::where('software', 'WMS')->where('deleted', 0)->first();

        // if (!$connection || !$connection->endpoint || !$connection->username || !$connection->password) {
        //     Log::error('No se encontraron credenciales válidas para WMS en api_connections.');
        //     throw new \Exception('Credenciales WMS no configuradas correctamente en la tabla api_connections.');
        // }

        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $this->baseUrl = rtrim($connection->endpoint, '/') . '/';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => false, // Cambiar a false para SSL auto-firmado
            'cookies' => true,
            'headers' => $this->defaultHeaders
        ]);

        // Guardar credenciales para uso en authenticate()
        $encryptionService = new EncryptionService();
        $this->wmsCredentials = [
            'UserName' => $connection->username,
            'Password' => $encryptionService->decrypt($connection->password),
        ];
    }

    private function authenticate()
    {
        try {
            $response = $this->client->post("auth/login", [
                'json' => [
                    "username" => $this->wmsCredentials['UserName'],
                    "password" => $this->wmsCredentials['Password']
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data) || !isset($data['token']) || empty($data['token'])) {
                throw new Exception('Error al obtener token en WMS.', 400);
            }

            $this->wmsToken = $data['token'];
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            if ($statusCode === 401) {
                throw new Exception('Error de autenticación en WMS: ' . $e->getMessage(), 401);
            }
        } catch (Exception $e) {
            throw new Exception('Error de configuración de cuenta API: ' . $e->getMessage(), 503);
        }
    }

    public function makeRequest(string $method, string $endpoint, array $data = [], array $params = []): array
    {
        try {
            // 1. Obtener token
            $this->authenticate();

            // 2. Validar método permitido
            $method = strtoupper($method);
            if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                return [
                    'success' => false,
                    'status_code' => 422,
                    'body' => null,
                    'error' => "Método de solicitud $method inválido"
                ];
            }

            // 3. Preparar opciones de solicitud
            $options = [
                'headers' => array_merge(
                    $this->defaultHeaders,
                    [
                        'Authorization' => 'Bearer ' . $this->wmsToken
                    ]
                )
            ];

            if (!empty($params) && $method === 'GET') {
                $options['query'] = $params;
            }

            if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $options['json'] = $data;
            }

            // 4. Guardar el request
            $this->lastRequest = [
                'method' => $method,
                'uri' => rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'),
                'headers' => $options['headers'],
                'params' => $params,
                'body' => in_array($method, ['POST', 'PUT', 'PATCH']) ? $data : null,
            ];

            Log::info(['ejecutando api wms' =>  [
                'method' => $method,
                'uri' => rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'),
                'headers' => $options['headers'],
                'params' => $params,
                'body' => in_array($method, ['POST', 'PUT', 'PATCH']) ? $data : null,
            ]]);

            // 5. Ejecutar request
            $response = $this->client->{strtolower($method)}($endpoint, $options);
            $responseData = json_decode($response->getBody()->getContents(), true);

            $this->lastResponse = [
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => $responseData,
            ];
            Log::info([
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'body' => $responseData,
                'error' => null
            ]);
            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'body' => $responseData,
                'error' => null
            ];
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();

            Log::error("Error HTTP en WMS [$method $endpoint] ($statusCode): $body");

            return [
                'success' => false,
                'status_code' => $statusCode,
                'body' => json_decode($body, true) ?? $body,
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("Error general en WMS [$method $endpoint]: " . $e->getMessage());

            return [
                'success' => false,
                'status_code' => $e->getCode() ?: 500,
                'body' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
