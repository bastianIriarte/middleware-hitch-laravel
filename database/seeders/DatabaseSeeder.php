<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Datos Test
        // $this->call(IntegrationSeeder::class);
        // Datos Reales
        $this->call(StatusIntegrationSeeder::class);
        $this->call(ApiConnectionSeeder::class);
        $this->call(ResourceSeeder::class);
        $this->call(IntegrationsViewSeeder::class);

        // File Management System
        $this->call(CompanySeeder::class);
        $this->call(FileTypeSeeder::class);
    }
}
