<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColumnsToIntegrationsTables extends Migration
{
    /**
     * Obtiene todas las tablas que comienzan con integrations_
     */
    private function getTables(): array
    {
        $results = DB::select("SHOW TABLES LIKE 'integrations_%'");

        return collect($results)
            ->map(function ($row) {
                // Detecta la clave dinámica, p.ej. Tables_in_family_shop (integrations_%)
                return collect((array) $row)->first();
            })
            ->filter(fn($name) => !empty($name))
            ->values()
            ->toArray();
    }

    /**
     * Ejecuta la migración
     */
    public function up(): void
    {
        $tables = $this->getTables();

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
                if (!Schema::hasColumn($tableName, 'entry_request_body')) {
                    $table->longText('entry_request_body')->nullable()->after('wms_response')->collation('utf8mb4_general_ci');
                }
                if (!Schema::hasColumn($tableName, 'entry_response')) {
                    $table->longText('entry_response')->nullable()->after('entry_request_body')->collation('utf8mb4_general_ci');
                }
            });

            echo "✅  Columnas agregadas en tabla: $tableName\n";
        }
    }

    /**
     * Revierte la migración
     */
    public function down(): void
    {
        $tables = $this->getTables();

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'entry_request_body')) {
                    $table->dropColumn('entry_request_body');
                }
                if (Schema::hasColumn($tableName, 'entry_response')) {
                    $table->dropColumn('entry_response');
                }
            });

            echo "🗑️  Columnas eliminadas en tabla: $tableName\n";
        }
    }
}
