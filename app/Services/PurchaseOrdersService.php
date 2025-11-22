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

class PurchaseOrdersService
{
    protected $sapService;

    public function __construct() {}
    /**
     * Preparar datos del socio de negocio según especificación OCRD
     */
    public function preparePurchaseOrderData(array $validatedData): array
    {
        #OPOR (Orden de compra)
        $fieldMapping = [
            'CardCode' => $validatedData['CardCode'],
            'DocDate' => $validatedData['DocDate'],
            'DocDueDate' => $validatedData['DocDueDate'],
            'NumAtCard' => $validatedData['NumAtCard'],
            'DocCurrency' => $validatedData['DocCurrency'] ?? "USD",

        ];
        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields($validatedData);

        $data = array_merge($fieldMapping, $arrUdf);

        #POR1 (Detalle Orden de compra)
        if (!empty($validatedData['lines']) && is_array($validatedData['lines'])) {
            $data['DocumentLines'] = array_map(function ($line) {
                $lineField = [
                    'ItemCode'        => $line['ItemCode'],
                    'Quantity'        => (float) ($line['Quantity'] ?? 0),
                    'Price'           => (float) ($line['Price'] ?? 0),
                    'WarehouseCode'   => $line['WhsCode'] ?? null,
                    'CostingCode'     => $line['OcrCode'] ?? null,
                    'CostingCode2'    => $line['OcrCode2'] ?? null,
                    'CostingCode3'    => $line['OcrCode3'] ?? null,
                    'TaxCode'         => $line['TaxCode'] ?? null,
                    'Currency'        => $line['Currency'] ?? "USD",
                ];
                $arrUdfDetail = $this->userDefinedFieldsDetail($line);
                $detail = array_merge($lineField, $arrUdfDetail);
                return $detail;
            }, $validatedData['lines']);
        }
        return $data;
    }


    public function preparePurchaseInvoiceData($request, array $purchaseOrderResponse)
    {
        $invoiceController = app(\App\Services\PurchaseInvoicesService::class);

        $fieldMapping = [
            # OPCH (Factura de reserva)
            "CardCode"          => $purchaseOrderResponse['CardCode'] ?? null,
            "DocDate"           => $request->input('InvoiceData.DocDate', $purchaseOrderResponse['DocDate'] ?? now()->format('Y-m-d')),
            "DocDueDate"        => $request->input('InvoiceData.DocDueDate', $purchaseOrderResponse['DocDueDate'] ?? now()->format('Y-m-d')),
            "TaxDate"           => $purchaseOrderResponse['TaxDate'] ?? now()->format('Y-m-d'),
            "FolioPrefixString" => data_get($request, 'InvoiceData.FolioPref'),
            "FolioNumber"       => data_get($request, 'InvoiceData.FolioNum'),
            'ReserveInvoice'    => 'tYES',
            'DocCurrency'       => $purchaseOrderResponse['DocCurrency'] ?? "USD",
        ];

        // UDFs de cabecera
        $arrUdf = $invoiceController->userDefinedFields((array) $request);
        $data   = array_merge($fieldMapping, $arrUdf);

        $data['DocumentLines'] = [];

        foreach ($purchaseOrderResponse['DocumentLines'] as $index => $line) {
            $lineField = [
                "ItemCode"      => $line['ItemCode'],
                "Quantity"      => (float) ($line['Quantity'] ?? 0),
                "Price"         => (float) ($line['Price'] ?? 0),
                "WarehouseCode" => $line['WarehouseCode'] ?? null,
                "TaxCode"       => $line['TaxCode'] ?? null,
                "CostingCode"   => $line['OcrCode'] ?? null,   // Dimensión 1
                "CostingCode2"  => $line['OcrCode2'] ?? null,  // Dimensión 2
                "CostingCode3"  => $line['OcrCode3'] ?? null,  // Dimensión 3
                "BaseType"      => '22',                       // 22 = Purchase Order
                "BaseEntry"     => $purchaseOrderResponse['DocEntry'] ?? null,
                "BaseLine"      => $index,
                "Currency"      => $line['Currency'] ?? "USD",
            ];

            // Obtengo los UDFs para esta línea
            $arrUdfDetail = $invoiceController->userDefinedFieldsDetail($line);

            // Uno ambos arrays
            $detail = array_merge($lineField, $arrUdfDetail);

            // Agrego el índice de línea
            $detail['LineNum'] = $index;

            // Inserto en el array final
            $data['DocumentLines'][] = $detail;
        }

        return $data;
    }


    public function userDefinedFields($data = [])
    {
        return [
            'U_ENCARGADO_COMPRA' => empty($data) ? 'U_ENCARGADO_COMPRA' : $data['U_ENCARGADO_COMPRA'],
            'U_INVOICE' => empty($data) ? 'U_INVOICE' : $data['U_INVOICE'],
            'U_BL' => empty($data) ? 'U_BL' : $data['U_BL'],
            'U_ETD' => empty($data) ? 'U_ETD' : $data['U_ETD'],
            'U_ETA' => empty($data) ? 'U_ETA' : $data['U_ETA'],
            'U_PEMBARQUE' => empty($data) ? 'U_PEMBARQUE' : $data['U_PEMBARQUE'],
            'U_PDESTINO' => empty($data) ? 'U_PDESTINO' : $data['U_PDESTINO'],
            'U_PCONSOLID' => empty($data) ? 'U_PCONSOLID' : $data['U_PCONSOLID'],
            'U_NSALIDA' => empty($data) ? 'U_NSALIDA' : $data['U_NSALIDA'],
            'U_NLLEGADA' => empty($data) ? 'U_NLLEGADA' : $data['U_NLLEGADA'],
            'U_FORWARDER' => empty($data) ? 'U_FORWARDER' : $data['U_FORWARDER'],
            'U_AGENCIA' => empty($data) ? 'U_AGENCIA' : $data['U_AGENCIA'],
            // 'U_CONTENEDOR' => empty($data) ? 'U_CONTENEDOR' : $data['U_CONTENEDOR'],
            // 'U_SELLO' => empty($data) ? 'U_SELLO' : $data['U_SELLO'],
            'U_INTEGRACION' => empty($data) ? 'U_INTEGRACION' : $data['U_INTEGRACION']
        ];
    }

    public function userDefinedFieldsDetail($data = [])
    {
        return [
            'U_COMPO_FINAL'   => $data['U_COMPO_FINAL']   ?? null,
            'U_SEI_Aprobador' => $data['U_SEI_Aprobador'] ?? null,
            'U_Integracion'   => $data['U_Integracion']   ?? null,
            'U_Status'        => $data['U_Status']        ?? null,
        ];
    }
}
