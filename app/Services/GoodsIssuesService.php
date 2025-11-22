<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Requests\Sap\ArticleStoreRequest;
use App\Http\Requests\Sap\ArticleUpdateRequest;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GoodsIssuesService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function prepareCreateData(array $validatedData) 
    {
        $data = [
            'DocDate' => $validatedData['DocDate'],
            'DocDueDate' => $validatedData['DocDueDate'],
            'TaxDate' => $validatedData['TaxDate'],
            'Reference2' => $validatedData['Reference2'],
            'Comments' => $validatedData['Comments']
        ];

        // pre_die($validatedData['lines']);

        // Líneas del documento
        if (isset($validatedData['lines']) && is_array($validatedData['lines'])) {
            $data['DocumentLines'] = [];

            foreach ($validatedData['lines'] as $line) {
                $documentLine = [
                    'ItemCode'      => $line['ItemCode'],
                    'Quantity'      => (float) $line['Quantity'] ?? 0,
                    'Price'         => (float) $line['Price'] ?? 0,
                    'WarehouseCode' => $line['WhsCode'],
                    'AccountCode' => $line['AccountCode'] ?? null,
                    'CostingCode' => $line['CostingCode'] ?? null,
                    'CostingCode2' => $line['CostingCode2'] ?? null,
                    'CostingCode3' => $line['CostingCode3'] ?? null,
                    
                ];

                $data['DocumentLines'][] = $documentLine;
            }
        }

        return $data;
    }

    public function sendData(array $data)
    {
        return $response = $this->sapService->post('/InventoryGenExits', $data);
    }
}