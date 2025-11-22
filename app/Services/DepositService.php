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

class DepositService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function prepareCreateData(array $validatedData)
    {
        $data = [
            'DepositType'      => $validatedData['DepositType'] ?? '', // tipo de depósito: actualmente ssolo se usa efectivo pero se deja disponible el campo por si se requere despues
            'DepositDate'      => $validatedData['DeposDate'],
            'DepositAccount'   => $validatedData['DposAcct'],
            'DepositorName'    => $validatedData['DpostorNam'],
            'Bank'             => $validatedData['DpsBank'],
            'BankBranch'       => $validatedData['DeposBrch'] ?? '',
            'BankAccountNum'   => $validatedData['BankAcc'],
            'AllocationAccount'=> $validatedData['AlocAcct'],
            'JournalRemarks'   => $validatedData['Memo'] ?? '',
            // 'Ref2'             => $validatedData['Ref2'] ?? '', // No existe para este modelo...
            'DepositCurrency'  => $validatedData['DeposCurr'] ?? null,
        ];

        // Lógica de montos
        if (!empty($validatedData['DeposCurr']) && $validatedData['DeposCurr'] != "CLP") {
            // Si hay moneda extranjera, TotalFC es obligatorio y TotalLC se puede omitir
            $data['TotalFC'] = $validatedData['DocTotalFC'];
            $data['TotalLC'] = $validatedData['DocTotalLC'] ?? null;
        } else {
            // Si no hay moneda extranjera, TotalLC es obligatorio y TotalFC se ignora
            $data['TotalLC'] = $validatedData['DocTotalLC'];
            $data['TotalFC'] = null;
        }

        return $data;
    }

    public function sendData(array $data)
    {
        return $response = $this->sapService->post('/Deposits', $data);
    }
}