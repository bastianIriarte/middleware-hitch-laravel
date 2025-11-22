<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class DepositStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'DeposCurr'  => 'nullable|string|size:3',
            'BankAcc'    => 'required|string|max:20',
            'DeposDate'  => 'required|date_format:Y-m-d',
            'DpsBank'    => 'required|string|max:50',
            'DeposBrch'  => 'required|string|max:50',
            'DposAcct'   => 'required|string|max:20',
            // 'Ref2'       => 'nullable|string|max:100',
            'DpostorNam' => 'required|string|max:100',
            'AlocAcct'   => 'required|string|max:20',
            'Memo'       => 'nullable|string|max:254',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',
            // Reglas condicionales para montos
            'DocTotalLC' => [
                'nullable',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    if (empty($this->DeposCurr) && $value === null) {
                        $fail('El monto en moneda local es obligatorio si no se indica moneda extranjera.');
                    }
                }
            ],
            'DocTotalFC' => [
                'nullable',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    if (!empty($this->DeposCurr) && $value === null) {
                        $fail('El monto en moneda extranjera es obligatorio si se indica moneda.');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'DeposCurr.size' => 'La moneda debe tener exactamente 3 caracteres.',

            'BankAcc.required'      => 'La cuenta del banco es obligatoria.',
            'BankAcc.max'           => 'La cuenta del banco no debe exceder los 20 caracteres.',

            'DeposDate.required'    => 'La fecha del depósito es obligatoria.',
            'DeposDate.date_format' => 'La fecha del depósito debe tener el formato YYYY-MM-DD.',

            'DpsBank.required'      => 'El nombre del banco es obligatorio.',
            'DpsBank.max'           => 'El nombre del banco no debe exceder los 50 caracteres.',

            'DeposBrch.required'    => 'La sucursal es obligatoria.',
            'DeposBrch.max'         => 'La sucursal no debe exceder los 50 caracteres.',

            'DposAcct.required'     => 'La cuenta bancaria es obligatoria.',
            'DposAcct.max'          => 'La cuenta bancaria no debe exceder los 20 caracteres.',

            // 'Ref2.max'              => 'La referencia no debe exceder los 100 caracteres.',

            'DpostorNam.required'   => 'El nombre del depositante es obligatorio.',
            'DpostorNam.max'        => 'El nombre del depositante no debe exceder los 100 caracteres.',

            'AlocAcct.required'     => 'La cuenta de caja es obligatoria.',
            'AlocAcct.max'          => 'La cuenta de caja no debe exceder los 20 caracteres.',

            'DocTotalLC.numeric'    => 'El monto en moneda local debe ser numérico.',
            'DocTotalLC.min'        => 'El monto en moneda local debe ser mayor que cero.',

            'DocTotalFC.numeric'    => 'El monto en moneda extranjera debe ser numérico.',
            'DocTotalFC.min'        => 'El monto en moneda extranjera debe ser mayor que cero.',

            'Memo.max'              => 'El comentario no debe exceder los 254 caracteres.',
        ];
    }
}
