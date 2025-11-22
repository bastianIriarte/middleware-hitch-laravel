<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FileTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fileTypes = [
            [
                'code' => 'CUSTOMERS',
                'name' => 'Clientes',
                'description' => 'Archivo de clientes con información de RUT, razón social, dirección, email, teléfono y coordenadas',
                'file_extension' => 'csv',
                'status' => true,
                'user_created' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRODUCTS',
                'name' => 'Productos',
                'description' => 'Archivo de productos con código, nombre, código Watts y factor de venta',
                'file_extension' => 'csv',
                'status' => true,
                'user_created' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'VENDORS',
                'name' => 'Vendedores',
                'description' => 'Archivo de vendedores con código y nombre',
                'file_extension' => 'csv',
                'status' => true,
                'user_created' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SELLOUT',
                'name' => 'Sell Out',
                'description' => 'Archivo de ventas con fecha, producto, cliente, cantidad, valor, costo, precio promedio y margen',
                'file_extension' => 'csv',
                'status' => true,
                'user_created' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($fileTypes as $fileType) {
            \App\Models\FileType::updateOrCreate(
                ['code' => $fileType['code']],
                $fileType
            );
        }
    }
}
