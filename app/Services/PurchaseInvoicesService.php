<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Requests\Sap\ArticleStoreRequest;
use App\Http\Requests\Sap\ArticleUpdateRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PurchaseInvoicesService
{
    protected $sapService;

    public function __construct() {}
    /**
     * Preparar datos del socio de negocio según especificación OCRD
     */
    public function prepareReserveInvoiceData(array $validatedData, $type = 'RESERVE_INVOICE'): array
    {

        $fieldMapping = [
            'CardCode' => $validatedData['CardCode'],
            'DocDate' => $validatedData['DocDate'],
            'DocDueDate' => $validatedData['DocDueDate'],
            'TaxDate' => $validatedData['TaxDate'],
            'FolioPrefixString' => $validatedData['FolioPref'] ?? null,
            'FolioNumber' => $validatedData['FolioNum'] ?? null,
            'DocCurrency' => $validatedData['DocCurrency'] ?? ($type == 'BILL' ? 'CLP': "USD"),

        ];

        switch ($type) {
            case 'INVOICE':
                $fieldMapping['ReserveInvoice'] = 'tNO';
                break;
            case 'BILL':
                $fieldMapping['DocumentSubType'] = 'bod_Bill';
                $fieldMapping['Comments'] = $validatedData['Comments'] ?? null;
                $fieldMapping['U_LOCAL'] = $validatedData['U_LOCAL'] ?? null;
                $fieldMapping['U_NUMCAJA'] = $validatedData['U_NUMCAJA'] ?? null;
                break;
            case 'RESERVE_INVOICE':
                $fieldMapping['ReserveInvoice'] = 'tYES';
                break;

            default:
                $fieldMapping['ReserveInvoice'] = 'tYES';
                break;
        }
        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields($validatedData);
        $data = array_merge($fieldMapping, $arrUdf);
        // Líneas del documento
        if (isset($validatedData['lines'])) {
            $data['DocumentLines'] = [];

            foreach ($validatedData['lines'] as $line) {
                // pre_die($line);
                $lineData = [
                    'ItemCode' => $line['ItemCode'],
                    'Quantity' => (float) $line['Quantity'],
                    'WarehouseCode' => $line['WhsCode'] ?? null,
                    'TaxCode' => $line['TaxCode'] ?? null,
                    'CostingCode' => $line['OcrCode'] ?? null,
                    'CostingCode2' => $line['OcrCode2'] ?? null,
                    'CostingCode3' => $line['OcrCode3'] ?? null,
                    'Currency'     => $line['Currency'] ?? ($type == 'BILL' ? 'CLP': "USD"),
                ];

                if ($type == 'BILL') {
                    $lineData['AccountCode'] = $line['AccountCode'] ?? null;
                    $lineData['PriceAfterVAT'] = (float) $line['PriceAfterVAT'] ?? 0;
                } else {
                    $lineData['Price'] = (float) $line['Price'] ?? 0;
                }

                if (in_array($type, ['INVOICE', 'RESERVE_INVOICE'])) {
                    $lineData['BaseType'] = $line['BaseType'] ?? null;
                    $lineData['BaseEntry'] = $line['BaseEntry'] ?? null;
                    $lineData['BaseLine'] = $line['BaseLine'] ?? null;
                }

                $data['DocumentLines'][] = $lineData;
            }
        }

        return $data;
    }

    public function userDefinedFields($data = [])
    {
        return [
            // 'U_INTEGRACION' => empty($data) ? 'U_INTEGRACION' : $data['U_INTEGRACION'],
            // 'U_STATUS' => empty($data) ? 'U_STATUS' : $data['U_STATUS'],
        ];
    }

    public function userDefinedFieldsDetail($data = [])
    {
        return [
            // 'U_SEI_CARPETA' => empty($data) ? 'U_SEI_CARPETA' : $data['U_SEI_CARPETA'],
        ];
    }
}
