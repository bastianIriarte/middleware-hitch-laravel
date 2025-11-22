<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $tables = [
            'articles',
            'business_partners',
            'deposits',
            'goods_issues',
            'goods_receipts',
            'journal_entries',
            'purchase_orders',
            'reserve_invoices',
            'returns',
            'stock_transfer',
            'stock_transfer_requests',
        ];

        // HTTP codes to cycle through for realism
        $httpCodes = [200, 201, 400, 401, 403, 404, 500];

        foreach ($tables as $suffix) {
            $table = "integrations_{$suffix}";
            $data = [];

            for ($i = 1; $i <= 10; $i++) {
                $code = $httpCodes[array_rand($httpCodes)];
                $status = $code >= 200 && $code < 300 ? 'success' : 'error';

                // Origen/destino
                $origin_destiny = ['SAP', 'WMS', 'FMMS'];

                // Simulated payloads
                $createPayload = [
                    'action' => 'create',
                    'entity' => ucfirst(str_replace('_', ' ', $suffix)),
                    'attributes' => [
                        'docEntry' => $i,
                        'cardCode' => 'C'.str_pad($i, 4, '0', STR_PAD_LEFT),
                        'quantity' => rand(1, 100),
                        'unitPrice' => rand(1000, 50000) / 100,
                        'currency' => ['USD', 'EUR', 'CLP'][array_rand(['USD', 'EUR', 'CLP'])],
                        'postingDate' => Carbon::now()->subDays(rand(0, 30))->toDateString(),
                    ],
                ];

                $requestPayload = [
                    'method' => 'POST',
                    'url' => "/api/{$suffix}",
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer your.jwt.token',
                    ],
                    'body' => $createPayload,
                ];

                $responsePayload = [
                    'status' => $status,
                    'code' => $code,
                    'data' => $status === 'success'
                        ? ['id' => $i, 'message' => 'Created successfully']
                        : ['error' => ['message' => 'An error occurred', 'details' => 'Invalid payload or unauthorized']],
                ];

                $data[] = [
                    'origin' => $origin_destiny[array_rand($origin_destiny)],
                    'destiny' => $origin_destiny[array_rand($origin_destiny)],
                    'create_body' => json_encode($createPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'request_body' => json_encode($requestPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'code' => (string) $code,
                    'message' => $responsePayload['status'] === 'success'
                        ? 'OK'
                        : 'Failed with code '.$code,
                    'response' => json_encode($responsePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'status_integration_id' => rand(1, 4),
                    'integration_log_id' => rand(1, 20),
                    'attempts' => rand(0, 3),
                    'user_created' => 1,
                    'created_at' => Carbon::now()->subMinutes(rand(0, 60)),
                    'user_updated' => null,
                    'updated_at' => null,
                    'user_deleted' => null,
                    'deleted' => 0,
                    'deleted_at' => null,
                ];
            }

            DB::table($table)->insert($data);
        }
    }
}
