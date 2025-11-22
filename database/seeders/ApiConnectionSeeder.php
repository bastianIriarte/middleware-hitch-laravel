<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiConnectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('api_connections')->insert([
            [
                'id' => 1,
                'software' => 'SAP',
                'endpoint' => null,
                'database' => null,
                'username' => null,
                'password' => null,
                'api_key' => null,
                'status' => 1,
                'user_created' => null,
                'created_at' => ahoraServidor(),
                'user_updated' => null,
                'updated_at' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'software' => 'WMS',
                'endpoint' => null,
                'database' => null,
                'username' => null,
                'password' => null,
                'api_key' => null,
                'status' => 1,
                'user_created' => null,
                'created_at' => ahoraServidor(),
                'user_updated' => null,
                'updated_at' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'software' => 'FMMS',
                'endpoint' => null,
                'database' => null,
                'username' => null,
                'password' => null,
                'api_key' => null,
                'status' => 1,
                'user_created' => null,
                'created_at' => ahoraServidor(),
                'user_updated' => null,
                'updated_at' => null,
                'user_deleted' => null,
                'deleted' => 0,
                'deleted_at' => null,
            ]
        ]);
    }
}
