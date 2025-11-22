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

class JournalEntriesService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function prepareCreateData(array $validatedData)
    {
        $data = [
            'ReferenceDate' => $validatedData['RefDate'],
            'DueDate' => $validatedData['DueDate'],
            'TaxDate' => $validatedData['TaxDate'],
            'Memo' => $validatedData['memo'],
        ];

        // Líneas del documento
        if (isset($validatedData['lines']) && is_array($validatedData['lines'])) {
            $data['JournalEntryLines'] = [];

            foreach ($validatedData['lines'] as $line) {
                $documentLine = [
                    'AccountCode' => $line['AccountCode'],
                    'LineMemo'    => $line['LineMemo'],
                    'DueDate'     => $line['DueDate'],
                    'ReferenceDate1' => $line['ReferenceDate1'],
                    'CostingCode'    => $line['CostingCode'] ?? null,
                    'CostingCode2'   => $line['CostingCode2'] ?? null,
                    'TaxDate'        => $line['TaxDate'],
                ];

                // Si viene en moneda local
                if (isset($line['Debit']) || isset($line['Credit'])) {
                    $documentLine['Debit']  = isset($line['Debit']) ? (float) $line['Debit'] : 0;
                    $documentLine['Credit'] = isset($line['Credit']) ? (float) $line['Credit'] : 0;
                }

                // Si viene en moneda extranjera
                if (isset($line['FCDebit']) || isset($line['FCCredit'])) {
                    $documentLine['FCDebit']   = isset($line['FCDebit']) ? (float) $line['FCDebit'] : 0;
                    $documentLine['FCCredit']  = isset($line['FCCredit']) ? (float) $line['FCCredit'] : 0;
                    $documentLine['FCCurrency'] = $line['FCCurrency'];
                }


                # --- Flujo de Caja (Cash Flow) ---
                if (isset($line['CashFlowLineItemID']) && !empty($line['CashFlowLineItemID'])) {

                    $cfwId = (int) $line['CashFlowLineItemID'];
                    $response = $this->sapService->get("/CashFlowLineItems({$cfwId})");
                    $item = $response['response'] ?? null;
                    if (!$item || !isset($item['LineItemID']) || ($item['ActiveLineItem'] ?? '') !== 'tYES') {
                        throw new Exception("El CashFlowLineItemID {$cfwId} no es válido o está inactivo.");
                    }

                    $fcDebit  = isset($line['FCDebit'])  ? (float) $line['FCDebit']  : 0.0;
                    $fcCredit = isset($line['FCCredit']) ? (float) $line['FCCredit'] : 0.0;
                    $lcDebit  = isset($line['Debit'])    ? (float) $line['Debit']    : 0.0;
                    $lcCredit = isset($line['Credit'])   ? (float) $line['Credit']   : 0.0;

                    $assignment = ['CashFlowLineItemID' => $cfwId];

                    #DIFINIMOS SI BIEN EN CLP O MONEDA EXTRANJERA
                    if (($fcDebit + $fcCredit) > 0) {
                        #FC 
                        $amountFC = $fcDebit > 0 ? $fcDebit : $fcCredit;
                        if (empty($documentLine['FCCurrency'])) {
                            throw new Exception('FCCurrency es obligatorio cuando usas AmountFC en CashFlowAssignments.');
                        }
                        $assignment['AmountFC']   = $amountFC;
                        $assignment['Currency']   = $documentLine['FCCurrency'];
                    } else {
                        #LC
                        $amountLC = $lcDebit > 0 ? $lcDebit : $lcCredit;
                        if ($amountLC <= 0) {
                            throw new Exception('La línea con CashFlowLineItemID debe tener un monto mayor a 0 (Debit o Credit).');
                        }
                        $assignment['AmountLC'] = $amountLC;
                    }
                    $documentLine['CashFlowAssignments'] = [$assignment];
                }
                $data['JournalEntryLines'][] = $documentLine;
            }
        }

        return $data;
    }

    public function sendData(array $data)
    {
        return $response = $this->sapService->post('/JournalEntries', $data);
    }
}
