<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $resources = [
            'articles' => 'Artículos',
            'business_partners' => 'Socios de Negocio',
            'deposits' => 'Depósitos',
            'goods_issues' => 'Salidas de Inventario',
            'goods_receipts' => 'Entradas de Mercadería',
            'journal_entries' => 'Asientos Contables',
            'purchase_orders' => 'Órdenes de Compra',
            'reserve_invoices' => 'Facturas de Reserva',
            'bill_invoices' => 'Boletas',
            'returns' => 'Devoluciones',
            'stock_transfer' => 'Transferencias de Stock',
            'stock_transfer_requests' => 'Solicitudes de Transferencia',
            'payments' => 'Pagos',
        ];

        foreach ($resources as $integration_table => $name) {

            $exists = DB::table('resources')->where('integrations_table', 'integrations_' . $integration_table)->exists();

            if (!$exists) {
                DB::table('resources')->insert([
                    'name' => $name,
                    'slug' => str_replace('_', '-', $integration_table),
                    'integrations_table' => 'integrations_' . $integration_table,
                    'status' => true,
                    'show_user' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
