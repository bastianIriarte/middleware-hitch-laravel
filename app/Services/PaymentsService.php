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

class PaymentsService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function prepareCreateData(array $validatedData)
    {
        $data = [
            'CardCode'       => $validatedData['CardCode'],
            'DocDate'        => $validatedData['DocDate'],
            'DueDate'        => $validatedData['DueDate'],
            'DocType'        => $validatedData['DocType'],
            'Remarks'        => $validatedData['Remarks'] ?? '',
            'CashAccount'    => $validatedData['CashAccount'] ?? '',
            'CashSum'        => (float) $validatedData['CashSum'] ?? 0,
        ];

        // Líneas del documento
        if (isset($validatedData['lines']) && is_array($validatedData['lines'])) {
            $data['PaymentInvoices'] = [];

            foreach ($validatedData['lines'] as $line) {
                $documentLine = [
                    'DocEntry'      => $line['DocEntry'],
                    'InvoiceType'   => $line['InvoiceType'],
                    'SumApplied'    => (float) $line['SumApplied'] ?? 0
                ];

                $data['PaymentInvoices'][] = $documentLine;
            }
        }

        /**
         * Tarjetas de crédito asociadas al pago
         */
        if (!empty($validatedData['credit_cards']) && is_array($validatedData['credit_cards'])) {
            $data['PaymentCreditCards'] = [];

            foreach ($validatedData['credit_cards'] as $index => $card) {
                $data['PaymentCreditCards'][] = [
                    'LineNum'              => $index,
                    'CreditCard'           => trim($card['CreditCard']),
                    'CreditAcct'           => trim($card['CreditAcct']),
                    'CreditCardNumber'     => trim($card['CreditCardNumber']),
                    'CardValidUntil'       => trim($card['CardValidUntil']),
                    'VoucherNum'           => trim($card['VoucherNum']) ?? null,
                    'OwnerIdNum'           => trim($card['OwnerIdNum']) ?? null,
                    'OwnerPhone'           => trim($card['OwnerPhone']) ?? null,
                    'PaymentMethodCode'    => trim($card['PaymentMethodCode']),
                    'NumOfPayments'        => trim($card['NumOfPayments']) ?? 1,
                    'FirstPaymentDue'      => trim($card['FirstPaymentDue']),
                    'FirstPaymentSum'      => (float) ($card['FirstPaymentSum'] ?? 0),
                    'AdditionalPaymentSum' => (float) ($card['AdditionalPaymentSum'] ?? 0),
                    'CreditSum'            => (float) ($card['CreditSum'] ?? 0),
                    'CreditCur'            => trim($card['CreditCur']) ?? 'CLP',
                    'CreditRate'           => (float) ($card['CreditRate'] ?? 0),
                    'ConfirmationNum'      => trim($card['ConfirmationNum']) ?? null,
                    'NumOfCreditPayments'  => (int) ($card['NumOfCreditPayments'] ?? 0),
                    'CreditType'           => trim($card['CreditType']) ?? 'cr_Regular',
                    'SplitPayments'        => $card['SplitPayments'] ?? 'tNO',
                ];
            }
        }

        return $data;
    }

    public function sendData(array $data)
    {
        return $response = $this->sapService->post('/IncomingPayments', $data);
    }
}