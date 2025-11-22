<?php

namespace App\Services;

use App\Models\ApiConnection;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SqlPosService
{
    protected $connectionName = 'sqlpos_connection';
    protected $connectionData;

    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }

        $this->connectionData = ApiConnection::where('software', 'SQL_POS')
            ->where('deleted', 0)
            ->first();
    }

    /**
     * Configurar la conexión en tiempo de ejecución.
     */
    private function configureConnection(
        $overrideHost = null,
        $overridePort = 1433,
        $overrideDatabase = null,
        $overrideUser = null,
        $overridePassword = null,
        $overrideDriver = "sqlsrv"
    ) {
        $encryptionService = app(\App\Services\EncryptionService::class);

        $host     = $overrideHost     ?? $this->connectionData->endpoint;
        $user     = $overrideUser     ?? $this->connectionData->username;
        $password = $overridePassword ?? $encryptionService->decrypt($this->connectionData->password);
        $database = $overrideDatabase ?? $this->connectionData->database;
        $port     = $overridePort     ?? ($this->connectionData->port ?? 1433);
        $driver   = $overrideDriver   ?? ($this->connectionData->driver ?? 'sqlsrv');

        config([
            "database.connections.{$this->connectionName}" => [
                'driver'   => $driver,
                'host'     => $host,
                'port'     => $port,
                'database' => $database,
                'username' => $user,
                'password' => $password,
                'charset'  => 'utf8',
                'prefix'   => '',
                'trust_server_certificate' => true,
            ]
        ]);

        return compact('driver', 'host', 'port', 'database', 'user');
    }

    /**
     * Testear la conexión con SQL_POS.
     */
    public function testConnection(
        $overrideHost = null,
        $overridePort = 1433,
        $overrideDatabase = null,
        $overrideUser = null,
        $overridePassword = null,
        $overrideDriver = "sqlsrv"
    ): array {
        try {
            $params = $this->configureConnection(
                $overrideHost,
                $overridePort,
                $overrideDatabase,
                $overrideUser,
                $overridePassword,
                $overrideDriver
            );

            DB::connection($this->connectionName)->getPdo();

            return [
                'success' => true,
                'message' => "Conexión a SQL_POS exitosa.",
                'params'  => $params,
                'error'   => null
            ];
        } catch (Exception $e) {
            Log::error("Error al probar conexión SQL_POS: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "No se pudo establecer conexión con SQL_POS.",
                'error'   => $e->getMessage()
            ];
        }
    }

    /**
     * Ejecutar una consulta SQL (SELECT).
     * 
     * Ejemplo de uso 1
     * $sql = "SELECT * FROM Productos WHERE Id = ? AND Categoria = ?";
     * $bindings = [123, 'Ropa'];
     * 
     * Ejemplo de uso 2
     * $sql = "SELECT * FROM Ventas WHERE Fecha >= :fechaInicio AND Fecha <= :fechaFin";
     * $bindings = ['fechaInicio' => '2025-08-01','fechaFin' => '2025-08-20'];
     * 
     * 
     */
    public function executeQuery(
        string $sql,
        array $bindings = [],
        ?string $overrideHost = null,
        ?string $overrideUser = null,
        ?string $overridePassword = null,
        ?string $overrideDatabase = null,
        ?int $overridePort = null,
        ?string $overrideDriver = null
    ): array {
        try {
            $this->configureConnection(
                $overrideHost,
                $overridePort,
                $overrideDatabase,
                $overrideUser,
                $overridePassword,
                $overrideDriver
            );

            $result = DB::connection($this->connectionName)->select($sql, $bindings);

            return [
                'success' => true,
                'data'    => $result,
                'error'   => null
            ];
        } catch (Exception $e) {
            Log::error("Error en executeQuery SQL_POS: " . $e->getMessage());

            return [
                'success' => false,
                'data'    => null,
                'error'   => $e->getMessage()
            ];
        }
    }

    /**
     * Ejecutar un statement (INSERT, UPDATE, DELETE).
     */
    public function executeStatement(
        $sql,
        $bindings = [],
        $overrideHost = null,
        $overridePort = null,
        $overrideDatabase = null,
        $overrideUser = null,
        $overridePassword = null,
        $overrideDriver = null
    ): array {
        try {
            $this->configureConnection(
                $overrideHost,
                $overridePort,
                $overrideDatabase,
                $overrideUser,
                $overridePassword,
                $overrideDriver
            );

            // Log de debug para ver la query y parámetros antes de ejecutarla
            Log::debug("Ejecutando executeStatement SQL_POS", [
                'sql'      => $sql,
                'bindings' => $bindings
            ]);

            $affected = DB::connection($this->connectionName)->affectingStatement($sql, $bindings);

            return [
                'success' => true,
                'data'    => $affected,
                'error'   => null
            ];
        } catch (Exception $e) {
            Log::error("Error en executeStatement SQL_POS: " . $e->getMessage());

            return [
                'success' => false,
                'data'    => null,
                'error'   => $e->getMessage()
            ];
        }
    }
}
