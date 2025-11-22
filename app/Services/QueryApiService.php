<?php

namespace App\Services;

use App\Models\ApiConnection;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class QueryApiService
{
    protected $client;
    protected $baseUrl;
    protected $timeout = 60;

    protected $apiKey;

    protected $defaultHeaders;

    public $lastRequest;
    public $lastResponse;

    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }

        // Buscar la conexión configurada para Query API
        $connection = ApiConnection::where('software', 'QUERY_API')->where('deleted', 0)->first();



        $this->baseUrl = isset($connection) ? rtrim($connection->endpoint, '/') . '/' : "";
        $this->apiKey = isset($connection) ? $connection->api_key : "";

        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-Key' => $this->apiKey,
        ];

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => false,
            'headers' => $this->defaultHeaders,
        ]);
    }

    public function executeQuery($sql,  $overrideUrl = null,  $overrideApiKey = null): array
    {
        $endpoint = "api/execute-query";

        try {

            $baseUrl = $overrideUrl ? rtrim($overrideUrl, '/') . '/' : $this->baseUrl;
            $apiKey  = $overrideApiKey ?? $this->apiKey;


            if (!$baseUrl || !$apiKey) {
                Log::error('No se encontraron credenciales válidas para QUERY_API en api_connections.');
                throw new \Exception('Credenciales Query API no configuradas correctamente en la tabla api_connections.');
            }
            
            $encryptionService = app(\App\Services\EncryptionService::class);
            $apiKey =  $encryptionService->decrypt($apiKey);

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-API-Key' => $apiKey,
            ];

            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout'  => $this->timeout,
                'verify'   => false,
                'headers'  => $headers,
            ]);


            $payload = ['Query' => $sql];

            $this->lastRequest = [
                'method'  => 'POST',
                'uri'     => $baseUrl . $endpoint,
                'headers' => $headers,
                'body'    => $payload,
            ];

            $response = $client->post($endpoint, ['json' => $payload]);
            $responseData = json_decode($response->getBody()->getContents(), true);

            $this->lastResponse = [
                'status_code' => $response->getStatusCode(),
                'headers'     => $response->getHeaders(),
                'body'        => $responseData,
            ];

            return [
                'success'     => true,
                'status_code' => $response->getStatusCode(),
                'body'        => $responseData,
                'error'       => null
            ];
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();

            Log::error("Error HTTP en Query API [POST /api/execute-query] ($statusCode): $body | mess" . $e->getMessage());

            return [
                'success' => false,
                'status_code' => $statusCode,
                'body' => json_decode($body, true) ?? $body,
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("Error general en Query API: " . $e->getMessage());

            return [
                'success' => false,
                'status_code' => $e->getCode() ?: 500,
                'body' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
