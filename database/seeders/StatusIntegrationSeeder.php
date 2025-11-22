<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'id' => 1,
                'status' => 'Pendiente',
                'description' => 'La integración está pendiente de ejecución.',
                'badge' => 'status-pending',
                'icon' => 'fa-clock-o',
                'user_created' => null,
                'user_updated' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'status' => 'Procesando',
                'description' => 'La integración se encuentra en proceso.',
                'badge' => 'status-processing',
                'icon' => 'fa-spinner',
                'user_created' => null,
                'user_updated' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'status' => 'Completado',
                'description' => 'La integración fue completada exitosamente.',
                'badge' => 'status-success',
                'icon' => 'fa-check-circle',
                'user_created' => null,
                'user_updated' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'status' => 'Fallido',
                'description' => 'La integración falló y requiere revisión.',
                'badge' => 'status-error',
                'icon' => 'fa-exclamation-circle',
                'user_created' => null,
                'user_updated' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'status' => 'Cerrado',
                'description' => 'La integración falló y fue marcada como cerrada.',
                'badge' => 'status-closed',
                'icon' => 'fa-ban',
                'user_created' => null,
                'user_updated' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
        ];

        DB::table('status_integrations')->insert($statuses);
    }
}
