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

class StockTransferRequestService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function prepareCreateData(array $validatedData)
    {
        $data = [
            'CardCode' => $validatedData['CardCode'],
            'DocDate' => $validatedData['DocDate'],
            'TaxDate' => $validatedData['TaxDate'],
            'FromWarehouse' => $validatedData['Filler'],
            'ToWarehouse' => $validatedData['ToWhsCode'],
            'U_INTEGRACION' => $validatedData['U_Integracion']
        ];

        // pre_die($validatedData['lines']);

        // Líneas del documento
        if (isset($validatedData['lines']) && is_array($validatedData['lines'])) {
            $data['StockTransferLines'] = [];

            foreach ($validatedData['lines'] as $line) {
                $documentLine = [
                    'ItemCode'      => $line['ItemCode'],
                    'Quantity'      => (float) $line['Quantity'] ?? 0,
                    'Price'         => (float) $line['Price'] ?? 0
                ];

                if (isset($line['WhsCode']) && !empty($line['WhsCode'])) {
                    $documentLine['WarehouseCode'] = $line['WhsCode'];
                }

                $data['StockTransferLines'][] = $documentLine;
            }
        }

        return $data;
    }

    public function sendData(array $data)
    {
        return $response = $this->sapService->post('/InventoryTransferRequests', $data);
    }
}
