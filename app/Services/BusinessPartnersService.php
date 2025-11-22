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

class BusinessPartnersService
{
    protected $sapService;

    public function __construct() {}
    /**
     * Preparar datos del socio de negocio según especificación OCRD
     */
    public function prepareBusinessPartnerData(array $validatedData): array
    {
        // Tabla OCRD - Datos principales según especificación
        $fieldMapping = [
            'CardCode' => $validatedData['CardCode'],           // Código del socio de negocio
            'CardName' => $validatedData['CardName'],           // Nombre del socio de negocio
            'CardType' => $validatedData['CardType'],           // Tipo de Socio de Negocio
            'GroupCode' => $validatedData['GroupCode'],         // Grupo Socio de Negocio
            'Currency' => $validatedData['Currency'],           // Moneda
            'FederalTaxID' => $validatedData['LicTradNum'],     // RUT (mapeo correcto para Service Layer)
            'Phone1' => $validatedData['Phone1'],               // Teléfono
            'EmailAddress' => $validatedData['E_Mail'],         // Mail
            'MailAddress' => $validatedData['E_Mail'],         // Mail
            'Notes' => $validatedData['Notes'] ?? null,         // Giro
            'PayTermsGrpCode' => $validatedData['GRouoNum'] ?? null,  // Condiciones de pago
            'PriceListNum' => $validatedData['ListNum'] ?? null,        // Lista de precio
            'DebitorAccount' => $validatedData['DebPayAcct'] ?? null,       // Cuenta contable asignada
            'Valid' => 'tYES',                                     // Activo por defecto
        ];


        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields($validatedData);

        $data = array_merge($fieldMapping, $arrUdf);

        // Tabla CRD1 - Direcciones
        if (isset($validatedData['Address'])) {
            $data['BPAddresses'] = [[
                'AddressName' => $validatedData['Address'],     // ID dirección
                'Street' => $validatedData['Street'],           // Calle / número
                'City' => $validatedData['City'],               // Ciudad
                'County' => $validatedData['County'] ?? null,   // Comuna
                'Country' => $validatedData['Country'],         // País
                'AddressType' => 'bo_BillTo'                    // Tipo de dirección
            ]];
        }

        // Tabla OCRB - Bancos (sin SWIFT temporalmente)
        if (isset($validatedData['BankCode']) && !empty($validatedData['BankCode'])) {
            $data['BPBankAccounts'] = [[
                'LogInstance' => 1,
                'BankCode' => $validatedData['BankCode'],       // Código Banco
                'AccountNo' => $validatedData['Account'],       // Cuenta (campo Account de tabla OCRB)
                // 'BICSwiftCode' => $validatedData['BICSwiftCode'] ?? ,  //TODO: AGREGAR EN CONTROLLER Y REQUEST (BCI CODE SWIFT)
            ]];
        }

        // Tabla OCPR - Personas de Contacto
        if (isset($validatedData['Contact']) && !empty($validatedData['Contact'])) {
            $data['ContactEmployees'] = [[
                'Name' => $validatedData['Contact']['Name'],            // ID Contacto (nombre)
                'Position' => $validatedData['Contact']['Position'] ?? null,  // Posición
                'Phone1' => $validatedData['Contact']['Tel'] ?? null,         // Teléfono
                'E_Mail' => $validatedData['Contact']['E_Mail'] ?? null       // Mail
            ]];
        }

        return $data;
    }

    public function prepareBusinessPartnerWmsData(array $validatedData): array
    {
        // Mapeo principal - estructura esperada por WMS
        if (isset($validatedData['LicTradNum']) && !empty($validatedData['LicTradNum'])) {
            $rutParts = preg_replace('/[^0-9kK]/', '', $validatedData['LicTradNum']); // Limpia puntos y guiones
            $rutNumero = substr($rutParts, 0, -1); // Todo menos el último carácter
            $rutDv = strtoupper(substr($rutParts, -1)); // Último carácter (DV)
        } else {
            $rutNumero = ''; // Todo menos el último carácter
            $rutDv = ''; // Último carácter (DV)
        }
        
        $fieldMapping = [
            'sn_code'      => $validatedData['CardCode'],                        // Código del SN
            'sn_nom'       => $validatedData['CardName'] ?? '',                        // Nombre o razón social
            'sn_tipo'      => isset($validatedData['CardType']) && !empty($validatedData['CardType']) ? $this->mapCardTypeToWms($validatedData['CardType']) : '', // Tipo (CL, PR, LD)
            'sn_rut'       => $rutNumero, // RUT sin dígito verificador
            'sn_dv'        => $rutDv,       // Dígito verificador
            'sn_direccion' => $validatedData['Street'] ?? '',                         // Calle
            'sn_comuna'    => $validatedData['County'] ?? '',                   // Comuna
            'sn_ciudad'    => $validatedData['City'] ?? '',                           // Ciudad
        ];

        return ['sn' => [$fieldMapping]];
    }

    private function mapCardTypeToWms(string $cardType): string
    {
        switch ($cardType) {
            case 'cCustomer':
                return 'CL';
            case 'cSupplier':
                return 'PR';
            case 'cLid':
                return 'LD';
            default:
                return 'CL';
        }
    }

    /**
     * Preparar datos para actualización de socio de negocio
     */
    public function prepareBusinessPartnerDataForUpdate(array $validatedData): array
    {
        $data = [];

        // Mapeo de campos permitidos para actualización
        $fieldMapping = [
            'CardName' => 'CardName',           // Nombre del socio de negocio
            'GroupCode' => 'GroupCode',         // Grupo Socio de Negocio
            'Currency' => 'Currency',           // Moneda
            'LicTradNum' => 'FederalTaxID',     // RUT
            'Phone1' => 'Phone1',               // Teléfono
            'E_Mail' => 'MailAddress',         // Mail
            'E_Mail' => 'EmailAddress',         // Mail
            'Notes' => 'Notes',                 // Giro
            'GRouoNum' => 'PayTermsGroupCode',  // Condiciones de pago
            'ListNum' => 'PriceListNum',        // Lista de precio
            'DebPayAcct' => 'DebitorAccount',       // Cuenta contable asignada
            'Valid' => 'Valid'                  // Estado
        ];

        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields();

        $fieldMapping = array_merge($fieldMapping, $arrUdf);

        // Solo incluir campos que están presentes en la solicitud
        foreach ($fieldMapping as $requestField => $sapField) {
            if (isset($validatedData[$requestField])) {
                $data[$sapField] = $validatedData[$requestField];
            }
        }

        // Mantener CardCode para identificación
        if (isset($validatedData['CardCode'])) {
            $data['CardCode'] = $validatedData['CardCode'];
        }

        // Direcciones si están presentes
        if (isset($validatedData['Address'])) {
            $data['BPAddresses'] = [[
                'AddressName' => $validatedData['Address'],
                'Street' => $validatedData['Street'],
                'City' => $validatedData['City'],
                'County' => $validatedData['County'] ?? null,
                'Country' => $validatedData['Country']
            ]];
        }

        // Contactos si están presentes
        if (isset($validatedData['Contact'])) {
            $data['ContactEmployees'] = [[
                'Name' => $validatedData['Contact']['Name'],
                'Position' => $validatedData['Contact']['Position'] ?? null,
                'Phone1' => $validatedData['Contact']['Tel'] ?? null,
                'E_Mail' => $validatedData['Contact']['E_Mail'] ?? null
            ]];
        }

        return $data;
    }



    public function userDefinedFields($data = [])
    {
        return [
            'U_INTEGRACION'  => empty($data) ? 'U_INTEGRACION' : $data['U_INTEGRACION'],
        ];
    }
}
