<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class IntegrationsViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = $this->getTables();

        if (empty($tables)) {
            $this->command->warn('⚠️ No se encontraron tablas con prefijo integrations_');
            return;
        }

        // 🔍 Mostrar tablas detectadas
        $this->command->info('🔍 Tablas detectadas: ' . implode(', ', $tables));

        $unionParts = [];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $unionParts[] = "
                    SELECT '$table' AS table_name, si.*
                    FROM `$table` AS si
                ";
            } else {
                Log::warning("⚠️ Tabla '$table' no existe, omitida al crear la vista integrations_view");
            }
        }

        if (empty($unionParts)) {
            $this->command->warn('⚠️ No hay tablas válidas para construir la vista integrations_view');
            return;
        }

        // Eliminamos la vista anterior si existe
        DB::statement("DROP VIEW IF EXISTS integrations_view");

        // Creamos la nueva vista
        $sql = "CREATE VIEW integrations_view AS " . implode(" UNION ALL ", $unionParts);
        DB::statement($sql);

        $this->command->info('✅ Vista [integrations_view] creada/actualizada correctamente.');
    }

    /**
     * Obtener tablas con prefijo integrations_
     */
    private function getTables(): array
    {
        $results = DB::select("SHOW TABLES LIKE 'integrations_%'");

        return collect($results)
            ->map(function ($row) {
                // La clave depende del nombre del schema, así que la obtenemos dinámicamente
                return collect((array) $row)->first();
            })
            ->filter(fn($name) => !empty($name))
            ->unique()
            ->values()
            ->toArray();
    }
}
