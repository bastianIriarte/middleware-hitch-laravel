<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessPartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $partner = $this->resource; // Datos originales del socio

        return [
            'CardCode'         => $partner['CardCode'] ?? null,
            'CardName'         => $partner['CardName'] ?? null,
            'CardType'         => $partner['CardType'] ?? null,
            'GroupCode'        => $partner['GroupCode'] ?? null,
            'Currency'         => $partner['Currency'] ?? null,
            'LicTradNum'       => $partner['FederalTaxID'] ?? null,
            'Phone1'           => $partner['Phone1'] ?? null,
            'E_Mail'           => $partner['EmailAddress'] ?? null,
            'Notes'            => $partner['Notes'] ?? null,
            'GRouoNum'         => $partner['PayTermsGroupCode'] ?? null,
            'PayTermsGrpCode'  => $partner['PayTermsGrpCode'] ?? null,
            'ListNum'          => $partner['PriceListNum'] ?? null,
            'DebPayAcct'   => $partner['DebitorAccount'] ?? null,
            'U_INTEGRACION'    => $partner['U_INTEGRACION'] ?? null,
            'BPAddresses'      => $this->transformBPAddresses($partner['BPAddresses'] ?? []),
            'Account'   => $this->transformBPBankAccounts($partner['BPBankAccounts'] ?? []),
            'Contact' => $this->transformContactEmployees($partner['ContactEmployees'] ?? []),
        ];
    }

    private function transformBPAddresses(array $addresses): array
    {
        return collect($addresses)->map(function ($address) {
            return [
                'AddressName' => $address['AddressName'] ?? null,
                'Street'      => $address['Street'] ?? null,
                'City'        => $address['City'] ?? null,
                'County'      => $address['County'] ?? null,
                'Country'     => $address['Country'] ?? null,
                'AddressType' => $address['AddressType'] ?? null,
            ];
        })->toArray();
    }

    private function transformBPBankAccounts(array $accounts): array
    {
        return collect($accounts)->map(function ($account) {
            return [
                'BankCode'    => $account['BankCode'] ?? null,
                'AccountNo'   => $account['AccountNo'] ?? null,
            ];
        })->toArray();
    }

    private function transformContactEmployees(array $contacts): array
    {
        return collect($contacts)->map(function ($contact) {
            return [
                'Name'    => $contact['Name'] ?? null,
                'Position'=> $contact['Position'] ?? null,
                'Tel'  => $contact['Phone1'] ?? null,
                'E_Mail'  => $contact['E_Mail'] ?? null,
            ];
        })->toArray();
    }
}
