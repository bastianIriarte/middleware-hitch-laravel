<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWmsColumnsToIntegrationTables extends Migration
{
    /**
     * Obtiene todas las tablas que comienzan con integrations_
     */
    private function getIntegrationTables(): array
    {
        $results = DB::select("SHOW TABLES LIKE 'integrations_%'");

        return collect($results)
            ->map(fn($row) => collect((array) $row)->first())
            ->filter(fn($name) => !empty($name) && $name !== 'integrations_view')
            ->values()
            ->toArray();
    }

    /**
     * Ejecuta la migración
     */
    public function up(): void
    {
        $tables = $this->getIntegrationTables();

        if (empty($tables)) {
            echo "⚠️  No se encontraron tablas integrations_*\n";
            return;
        }

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                echo "⏭️  Tabla $tableName no existe, se omite.\n";
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'includes_wms_integration')) {
                    $table->tinyInteger('includes_wms_integration')
                        ->default(0)
                        ->after('caller_method');
                }
                if (!Schema::hasColumn($tableName, 'wms_request_body')) {
                    $table->longText('wms_request_body')
                        ->nullable()
                        ->collation('utf8mb4_general_ci')
                        ->after('includes_wms_integration');
                }
                if (!Schema::hasColumn($tableName, 'wms_code')) {
                    $table->string('wms_code', 255)
                        ->nullable()
                        ->collation('utf8mb4_general_ci')
                        ->after('wms_request_body');
                }
                if (!Schema::hasColumn($tableName, 'wms_response')) {
                    $table->longText('wms_response')
                        ->nullable()
                        ->collation('utf8mb4_general_ci')
                        ->after('wms_code');
                }
            });

            echo "✅  Columnas WMS agregadas en tabla: $tableName\n";
        }
    }

    /**
     * Revierte la migración
     */
    public function down(): void
    {
        $tables = $this->getIntegrationTables();

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'includes_wms_integration')) {
                    $table->dropColumn('includes_wms_integration');
                }
                if (Schema::hasColumn($tableName, 'wms_request_body')) {
                    $table->dropColumn('wms_request_body');
                }
                if (Schema::hasColumn($tableName, 'wms_code')) {
                    $table->dropColumn('wms_code');
                }
                if (Schema::hasColumn($tableName, 'wms_response')) {
                    $table->dropColumn('wms_response');
                }
            });

            echo "🗑️  Columnas WMS eliminadas en tabla: $tableName\n";
        }
    }
}
