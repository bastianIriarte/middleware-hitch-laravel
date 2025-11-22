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

class StockTransferService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function prepareCreateData(array $validatedData)
    {

        $data = [];

        $fieldMapping = [
            'CardCode' => $validatedData['CardCode'],
            'DocDate' => $validatedData['DocDate'],
            'TaxDate' => $validatedData['TaxDate'],
            'FromWarehouse' => $validatedData['Filler'],
            'ToWarehouse' => $validatedData['ToWhsCode'],
            'Series' => $validatedData['Series'] ?? 27,
        ];

        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields($validatedData);

        $data = array_merge($fieldMapping, $arrUdf);


     

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


    public function userDefinedFields($data = [])
    {
        return [
            'U_INTEGRACION'       => !empty($data) ? $data['U_Integracion'] : null,
            'U_BFE_TipoDoctoRef'  => !empty($data) ? $data['U_BFE_TipoDoctoRef'] : null,
            'U_BFE_FechaRef'      => !empty($data) ? $data['U_BFE_FechaRef'] : null,
            'U_BFE_IndTraslado'   => !empty($data) ? $data['U_BFE_IndTraslado'] : null,
            'U_BFE_RutChofer'     => !empty($data) ? $data['U_BFE_RutChofer'] : null,
            'U_BFE_NombreChofer'  => !empty($data) ? $data['U_BFE_NombreChofer'] : null,
            'U_BFE_RutTrasporte'  => !empty($data) ? $data['U_BFE_RutTrasporte'] : null,
            'U_BFE_Patente'       => !empty($data) ? $data['U_BFE_Patente'] : null,
        ];
    }

    public function sendData(array $data)
    {
        return $response = $this->sapService->post('/StockTransfers', $data);
    }
}
