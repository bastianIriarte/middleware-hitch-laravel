<?php

namespace App\Helpers;

use App\Models\Integration;
use Exception;
use Illuminate\Support\Facades\Log;

class IntegrationLogger
{   
    /**
     * Crea un nuevo log en la tabla de integración correspondiente.
     */
    public static function create(string $table, array $data): array
    {
        
        $prefixedTable = 'integrations_' . $table;
        // Obtener el método que invoca
        $callerMethod = self::getCallerMethod();

        try {
            $data['caller_method'] = $callerMethod ?? null;

            $data['user_created'] = $data['user_created'] ?? auth()->user()->id ?? null;
            $data['created_at'] = ahoraServidor();

            $log = new Integration();
            $log->setTableName($prefixedTable);
            $log->fill($data);
            $log->save();

            Log::info("[IntegrationLogger:create] Registro exitoso en {$prefixedTable}", [
                'id' => $log->id,
                'status' => $data['status_integration_id'] ?? null,
                'message' => $data['message'] ?? null,
            ]);

            return [
                'result' => true,
                'message' => '',
                'data' => $log
            ];
        } catch (Exception $e) {
            Log::info("ERROR: [IntegrationLogger:create] Error en {$prefixedTable}: " . $e->getMessage(), [
                'data' => $data,
            ]);
            return [
                'result' => false,
                'message' => "ERROR: [IntegrationLogger:create] Error en {$prefixedTable}: " . $e->getMessage(),
                'data' =>[]
            ];
        }
    }

     /**
     * Actualiza un log existente.
     */
    public static function update(string $table, int $id, array $data): array
    {
        $prefixedTable = 'integrations_' . $table;

        try {
            $data['user_updated'] = $data['user_updated'] ?? auth()->user()->id ?? null;
            $data['updated_at'] = ahoraServidor();

            $log = (new Integration())->setTableName($prefixedTable)->find($id);
            if (!$log) {
                $msg = "[IntegrationLogger:update] Log ID {$id} no encontrado en {$prefixedTable}";
                Log::info($msg);
                return [
                    'result' => false,
                    'message' => $msg,
                    'data' => []
                ];
            }

            $log->update($data);

            Log::info("[IntegrationLogger:update] Log actualizado correctamente en {$prefixedTable}", [
                'id' => $id,
                'status' => $data['status_integration_id'] ?? null,
                'message' => $data['message'] ?? null,
            ]);

            return [
                'result' => true,
                'message' => '',
                'data' => $log
            ];
        } catch (Exception $e) {
            Log::info("ERROR: [IntegrationLogger:update] Error al actualizar log en {$prefixedTable}: " . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
            ]);
            return [
                'result' => false,
                'message' => "ERROR: [IntegrationLogger:update] Error en {$prefixedTable}: " . $e->getMessage(),
                'data' => []
            ];
        }
    }

    private static function getCallerMethod(): string
    {
        try {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            foreach ($trace as $frame) {
                if (
                    isset($frame['class']) &&
                    !str_starts_with($frame['class'], 'Illuminate\\') &&
                    !str_starts_with($frame['class'], 'Laravel\\')
                ) {
                    $class = $frame['class'] ?? 'N/A';
                    $function = $frame['function'] ?? 'N/A';

                    if (str_starts_with($class, 'App\Http\Controllers\Api')) {
                        \Log::info("Método invocado desde {$class}@{$function}");
                        return "$class@$function";
                    }
                }
            }

            \Log::warning("No se encontró ningún controlador invocador en el backtrace.");
            return 'unknown@unknown';
        } catch (\Throwable $e) {
            \Log::error("Error al obtener el método invocador: " . $e->getMessage());
            return 'error@trace';
        }
    }
}