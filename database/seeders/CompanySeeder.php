<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = [
            [
                'code' => 'WATTS',
                'name' => 'Watts S.A.',
                'rut' => '76123456-7',
                'email' => 'contacto@watts.cl',
                'phone' => '+56 2 2345 6789',
                'address' => 'Santiago, Chile',
                'status' => true,
                'user_created' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EMPRESA_DEMO',
                'name' => 'Empresa Demo',
                'rut' => '76987654-3',
                'email' => 'demo@empresa.cl',
                'phone' => '+56 2 9876 5432',
                'address' => 'Santiago, Chile',
                'status' => true,
                'user_created' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($companies as $company) {
            \App\Models\Company::updateOrCreate(
                ['code' => $company['code']],
                $company
            );
        }
    }
}
