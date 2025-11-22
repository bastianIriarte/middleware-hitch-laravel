<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class BusinessPartnerUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // ===== TABLA OCRD - CAMPOS ACTUALIZABLES =====
            'CardCode'     => 'required|string|max:15',     // Código del socio de negocio (NVARCHAR 15)
            'CardName'     => 'sometimes|string|max:100',                    // Nombre del socio de negocio
            'GroupCode'    => 'sometimes|integer',                // Grupo Socio de Negocio
            'Currency'     => 'sometimes|string|size:3',                     // Moneda
            'LicTradNum'   => 'sometimes|string|max:32',                     // RUT
            'Phone1'       => 'sometimes|string|max:20',                     // Teléfono
            'E_Mail'       => 'sometimes|email|max:100',                     // Mail
            'Notes'        => 'sometimes|nullable|string|max:200',           // Giro
            'GRouoNum'     => 'sometimes|nullable|integer',                  // Condiciones de pago
            'ListNum'      => 'sometimes|nullable|integer',                  // Lista de precio
            'DebPayAcct'   => 'sometimes|nullable|string|max:50',            // Cuenta contable asignada

            // Campos adicionales OITM
            'U_INTEGRACION'  => 'sometimes|string|in:S,N',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',

            // ===== TABLA CRD1 - DIRECCIONES =====
            'Address'      => 'sometimes|string|max:50',                     // ID dirección (AJUSTADO de 100 → 50)
            'Street'       => 'sometimes|string|max:100',                    // Calle / número
            'City'         => 'sometimes|string|max:100',                    // Ciudad
            'County'       => 'sometimes|nullable|string|max:100',           // Comuna
            'Country'      => 'sometimes|string|size:2',                     // País (AJUSTADO de 2 → 3, ISO alfa-3)

            // ===== TABLA OCPR - CONTACTOS =====
            'Contact'                 => 'sometimes|array',                  // Datos del contacto
            'Contact.Name'            => 'required_with:Contact|string|max:50', // ID Contacto
            'Contact.Position'        => 'sometimes|nullable|string|max:90',     // Posición
            'Contact.Tel'             => 'sometimes|nullable|string|max:20',     // Teléfono
            'Contact.E_Mail'          => 'sometimes|nullable|email|max:100',     // Mail
        ];
    }

    public function messages()
    {
        return [
            // ===== MENSAJES TABLA OCRD =====
            'CardCode.required' => 'El código del socio de negocio es obligatorio.',
            'CardCode.max'      => 'El código del socio de negocio no debe exceder los 15 caracteres.',
            
            'CardName.string'   => 'El nombre o razón social debe ser una cadena de texto.',
            'CardName.max'      => 'El nombre o razón social no debe exceder los 100 caracteres.',

            'GroupCode.integer' => 'El grupo debe ser un número entero.',
            'GroupCode.in'      => 'El grupo debe ser: 100 (local) o 108 (extranjero).',

            'Currency.string'   => 'La moneda debe ser una cadena de texto.',
            'Currency.size'     => 'La moneda debe tener exactamente 3 caracteres.',

            'LicTradNum.string' => 'El RUT debe ser una cadena de texto.',
            'LicTradNum.max'    => 'El RUT no debe exceder los 32 caracteres.',

            'Phone1.string'     => 'El teléfono debe ser una cadena de texto.',
            'Phone1.max'        => 'El teléfono no debe exceder los 20 caracteres.',

            'E_Mail.email'      => 'El correo debe tener un formato válido.',
            'E_Mail.max'        => 'El correo no debe exceder los 100 caracteres.',

            'Notes.string'      => 'El giro debe ser una cadena de texto.',
            'Notes.max'         => 'El giro no debe exceder los 200 caracteres.',

            'GRouoNum.integer'  => 'El código de condiciones de pago debe ser un número entero.',
            'ListNum.integer'   => 'El código de lista de precio debe ser un número entero.',

            'DebPayAcct.string' => 'La cuenta contable debe ser una cadena de texto.',
            'DebPayAcct.max'    => 'La cuenta contable no debe exceder los 50 caracteres.',

            // Campos adicionales OITM
            'U_INTEGRACION.string'   => 'El origen del dato debe ser una cadena de texto.',
            'U_INTEGRACION.in'       => 'El origen del dato debe ser S (Integración) o N (Manual).',


            // ===== MENSAJES TABLA CRD1 =====
            'Address.string' => 'La dirección debe ser una cadena de texto.',
            'Address.max'    => 'La dirección no debe exceder los 50 caracteres.',

            'Street.string'  => 'La calle debe ser una cadena de texto.',
            'Street.max'     => 'La calle no debe exceder los 100 caracteres.',

            'City.string'    => 'La ciudad debe ser una cadena de texto.',
            'City.max'       => 'La ciudad no debe exceder los 100 caracteres.',

            'County.string'  => 'La comuna debe ser una cadena de texto.',
            'County.max'     => 'La comuna no debe exceder los 100 caracteres.',

            'Country.string' => 'El país debe ser una cadena de texto.',
            'Country.size'   => 'El país debe tener exactamente 2 caracteres (código alfa-2).',

            // ===== MENSAJES TABLA OCPR =====
            'Contact.Name.required_with'     => 'El nombre del contacto es obligatorio cuando se incluye información de contacto.',
            'Contact.Name.string'            => 'El nombre del contacto debe ser una cadena de texto.',
            'Contact.Name.max'               => 'El nombre del contacto no debe exceder los 50 caracteres.',
            'Contact.Position.string'            => 'La posición debe ser una cadena de texto.',
            'Contact.Position.max'               => 'La posición no debe exceder los 90 caracteres.',
            'Contact.Tel.string'                 => 'El teléfono del contacto debe ser una cadena de texto.',
            'Contact.Tel.max'                    => 'El teléfono del contacto no debe exceder los 20 caracteres.',
            'Contact.E_Mail.email'               => 'El correo del contacto debe tener un formato válido.',
            'Contact.E_Mail.max'                 => 'El correo del contacto no debe exceder los 100 caracteres.',
        ];
    }
}
