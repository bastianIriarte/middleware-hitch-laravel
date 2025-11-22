<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SapErrorHandlerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            // Log del error
            Log::error('SAP API Error', [
                'endpoint' => $request->getUri(),
                'method' => $request->getMethod(),
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mapear errores comunes de SAP a códigos HTTP apropiados
            $statusCode = $this->mapSapErrorToHttpStatus($e->getCode());
            $errorMessage = $this->formatSapError($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $e->getCode(),
                'timestamp' => now()->toISOString()
            ], $statusCode);
        }
    }

    /**
     * Mapear códigos de error de SAP a códigos HTTP
     */
    private function mapSapErrorToHttpStatus(int $errorCode): int
    {
        $errorMappings = config('sap.error_codes', []);

        if (in_array($errorCode, $errorMappings['duplicate_key'] ?? [])) {
            return 409; // Conflict
        }

        if (in_array($errorCode, $errorMappings['not_found'] ?? [])) {
            return 404; // Not Found
        }

        if (in_array($errorCode, $errorMappings['invalid_value'] ?? [])) {
            return 422; // Unprocessable Entity
        }

        if (in_array($errorCode, $errorMappings['permission_denied'] ?? [])) {
            return 403; // Forbidden
        }

        // Códigos HTTP conocidos
        if ($errorCode >= 400 && $errorCode <= 599) {
            return $errorCode;
        }

        return 500; // Internal Server Error por defecto
    }

    /**
     * Formatear mensajes de error de SAP para ser más amigables
     */
    private function formatSapError(string $errorMessage): string
    {
        // Remover prefijos técnicos comunes de SAP
        $errorMessage = preg_replace('/^.*?\[Message\]\s*/', '', $errorMessage);
        $errorMessage = preg_replace('/^.*?Error:\s*/', '', $errorMessage);

        // Mapear errores comunes a mensajes más amigables
        $errorMappings = [
            'Item already exists' => 'El artículo ya existe en el sistema',
            'Business Partner already exists' => 'El socio de negocio ya existe',
            'Invalid warehouse code' => 'Código de almacén inválido',
            'Invalid item group' => 'Grupo de artículos inválido',
            'Login failed' => 'Error de autenticación con SAP',
            'Session expired' => 'Sesión expirada, intente nuevamente',
        ];

        foreach ($errorMappings as $sapError => $friendlyError) {
            if (stripos($errorMessage, $sapError) !== false) {
                return $friendlyError;
            }
        }

        return $errorMessage;
    }
}