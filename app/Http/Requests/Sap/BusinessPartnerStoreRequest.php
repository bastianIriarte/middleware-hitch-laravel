<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class BusinessPartnerStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // ===== TABLA OCRD - SOCIO DE NEGOCIO =====
            'CardCode'     => 'required|string|max:15',     // Código del socio de negocio (NVARCHAR 15)
            'CardName'     => 'required|string|max:100',    // Nombre del socio de negocio (NVARCHAR 100)
            'CardType'     => 'required|string|in:cCustomer,cSupplier,cLid',   // Tipo de Socio de Negocio cCustomer (Cliente), cSupplier (Proveedor), cLid (Lead).
            'GroupCode'    => 'required|integer',           // Grupo Socio de Negocio
            'LicTradNum'   => 'required|string|max:32',     // RUT (NVARCHAR 32)
            'Phone1'       => 'required|string|max:20',     // Teléfono (NVARCHAR 20)
            'E_Mail'       => 'required|email|max:100',     // Mail (NVARCHAR 100)
            'Notes'        => 'nullable|string|max:200',    // Giro (NVARCHAR 200)
            'GRouoNum'     => 'nullable|integer',           // Condiciones de pago
            'ListNum'      => 'nullable|integer',           // Lista de precio
            'DebPayAcct'   => 'nullable|string|max:50',     // Cuenta contable asignada (NVARCHAR 50),
            'Currency'     => 'required|string|max:3',      // Cuenta contable asignada (NVARCHAR 50),

            // Campos adicionales OITM
            'U_INTEGRACION'  => 'nullable|string|in:S,N',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',

            // ===== TABLA CRD1 - DIRECCIONES =====
            'Address'      => 'required|string|max:50',     // ID dirección (NVARCHAR 50)
            'Street'       => 'required|string|max:100',    // Calle / número (NVARCHAR 100)
            'City'         => 'required|string|max:100',    // Ciudad (NVARCHAR 100)
            'County'       => 'nullable|string|max:100',    // Comuna (NVARCHAR 100)
            'Country'      => 'required|string|size:2',     // País (NVARCHAR 3)

            // ===== TABLA OCRB - BANCOS =====
            'BankCode'     => 'required|string|max:30',     // Código Banco (NVARCHAR 30)
            'Account'      => 'required|string|max:50',     // Cuenta (NVARCHAR 50)

            // ===== TABLA OCPR - CONTACTOS =====
            'Contact'         => 'required|array',          // Datos del contacto
            'Contact.Name'    => 'required|string|max:50', // ID Contacto (NVARCHAR 100)
            'Contact.Position' => 'nullable|string|max:90',  // Posición (NVARCHAR 50)
            'Contact.Tel'     => 'nullable|string|max:20',  // Teléfono (NVARCHAR 20)
            'Contact.E_Mail'  => 'nullable|email|max:100',  // Mail (NVARCHAR 100)
        ];
    }

    public function messages()
    {
        return [
            // ===== MENSAJES TABLA OCRD =====
            'CardCode.required' => 'El código del socio de negocio es obligatorio.',
            'CardCode.max'      => 'El código del socio de negocio no debe exceder los 15 caracteres.',

            'CardName.required' => 'El nombre o razón social es obligatorio.',
            'CardName.max'      => 'El nombre o razón social no debe exceder los 100 caracteres.',

            'CardType.required' => 'El tipo de socio de negocio es obligatorio.',
            'CardType.in'       => 'El tipo debe ser: cCustomer (Cliente), cSupplier (Proveedor), cLid (Lead).',

            'GroupCode.required' => 'El grupo del socio de negocio es obligatorio.',
            'GroupCode.in'      => 'El grupo debe ser: 100 (local) o 108 (extranjero).',

            'Currency.required' => 'La moneda es obligatoria.',
            'Currency.size'     => 'La moneda debe tener exactamente 3 caracteres (ej: USD, CLP, EUR).',

            'LicTradNum.required' => 'El RUT es obligatorio.',
            'LicTradNum.max'    => 'El RUT no debe exceder los 32 caracteres.',

            'Phone1.required'   => 'El teléfono es obligatorio.',
            'Phone1.max'        => 'El teléfono no debe exceder los 20 caracteres.',

            'E_Mail.required'   => 'El correo electrónico es obligatorio.',
            'E_Mail.email'      => 'El correo debe tener un formato válido.',
            'E_Mail.max'        => 'El correo no debe exceder los 100 caracteres.',

            'Notes.max'         => 'El giro no debe exceder los 200 caracteres.',

            'GRouoNum.integer'  => 'El código de condiciones de pago debe ser un número entero.',
            'ListNum.integer'   => 'El código de lista de precio debe ser un número entero.',

            'DebPayAcct.string' => 'La cuenta contable debe ser una cadena de texto.',
            'DebPayAcct.max'    => 'La cuenta contable no debe exceder los 50 caracteres.',

            'U_INTEGRACION.required' => 'El origen del dato es obligatorio.',
            'U_INTEGRACION.string'   => 'El origen del dato debe ser una cadena de texto.',
            'U_INTEGRACION.in'       => 'El origen del dato debe ser S (Integración) o N (Manual).',

            // ===== MENSAJES TABLA CRD1 =====
            'Address.required'  => 'La dirección es obligatoria.',
            'Address.max'       => 'La dirección no debe exceder los 50 caracteres.',

            'Street.required'   => 'La calle/número es obligatoria.',
            'Street.max'        => 'La calle no debe exceder los 100 caracteres.',

            'City.required'     => 'La ciudad es obligatoria.',
            'City.max'          => 'La ciudad no debe exceder los 100 caracteres.',

            'County.max'        => 'La comuna no debe exceder los 100 caracteres.',

            'Country.required'  => 'El país es obligatorio.',
            'Country.size'      => 'El país debe tener exactamente 2 caracteres (código alfa-2, ej: CL, US, CN).',

            // ===== MENSAJES TABLA OCRB =====
            'BankCode.required'   => 'El código del banco es obligatorio.',
            'BankCode.max'        => 'El código del banco no debe exceder los 30 caracteres.',

            'Account.required'    => 'El número de cuenta es obligatorio.',
            'Account.max'         => 'El número de cuenta no debe exceder los 50 caracteres.',

            // ===== MENSAJES TABLA OCPR =====
            'Contact.required'          => 'Los datos del contacto son obligatorios.',
            'Contact.Name.required'     => 'El nombre del contacto es obligatorio.',
            'Contact.Name.max'          => 'El nombre del contacto no debe exceder los 50 caracteres.',
            'Contact.Position.max'      => 'La posición no debe exceder los 90 caracteres.',
            'Contact.Tel.max'           => 'El teléfono del contacto no debe exceder los 20 caracteres.',
            'Contact.E_Mail.email'      => 'El correo del contacto debe tener un formato válido.',
            'Contact.E_Mail.max'        => 'El correo del contacto no debe exceder los 100 caracteres.',
        ];
    }
}
