<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class JournalEntryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Encabezado (OJDT)
            'RefDate'       => 'required|date_format:Y-m-d',  // Fecha contabilización
            'DueDate'       => 'required|date_format:Y-m-d',  // Fecha vencimiento
            'TaxDate'       => 'required|date_format:Y-m-d',  // Fecha documento
            'memo'          => 'required|string|max:254',     // Glosa
            'ORIGEN_PETICION'  => 'nullable|string|max:50',

            // Detalle (JDT1)
            'lines' => 'required|array|min:1',
            'lines.*.AccountCode'   => 'required|string|max:15',
            'lines.*.Debit'         => 'nullable|numeric|min:0',
            'lines.*.Credit'        => 'nullable|numeric|min:0',
            'lines.*.FCDebit'       => 'nullable|numeric|min:0',
            'lines.*.FCCredit'      => 'nullable|numeric|min:0',
            'lines.*.FCCurrency'    => 'nullable|string|max:50',
            'lines.*.DueDate'       => 'nullable|date_format:Y-m-d',
            'lines.*.LineMemo'      => 'nullable|string|max:254',
            'lines.*.ReferenceDate1'=> 'nullable|date_format:Y-m-d',
            'lines.*.CostingCode'   => 'nullable|string|max:50',
            'lines.*.TaxDate'       => 'nullable|date_format:Y-m-d',
            'lines.*.CostingCode2'  => 'nullable|string|max:50',
            'lines.*.CashFlowLineItemID' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'RefDate.required' => 'La fecha de contabilización es obligatoria.',
            'RefDate.date_format' => 'La fecha de contabilización debe tener el formato YYYY-MM-DD.',

            'DueDate.required' => 'La fecha de vencimiento es obligatoria.',
            'DueDate.date_format' => 'La fecha de vencimiento debe tener el formato YYYY-MM-DD.',

            'TaxDate.required' => 'La fecha del documento es obligatoria.',
            'TaxDate.date_format' => 'La fecha del documento debe tener el formato YYYY-MM-DD.',

            'memo.required' => 'La glosa del asiento es obligatoria.',
            'memo.max' => 'La glosa no debe exceder los 254 caracteres.',

            'lines.required' => 'Debe incluir al menos una línea contable.',
            'lines.array' => 'El campo de líneas debe ser un arreglo.',

            'lines.*.AccountCode.required' => 'La cuenta contable es obligatoria.',
            'lines.*.AccountCode.max' => 'La cuenta contable no debe exceder los 15 caracteres.',

            'lines.*.Debit.numeric' => 'El monto en debe debe ser un número.',
            'lines.*.Debit.min' => 'El monto en debe no puede ser negativo.',

            'lines.*.Credit.numeric' => 'El monto en haber debe ser un número.',
            'lines.*.Credit.min' => 'El monto en haber no puede ser negativo.',

            'lines.*.FCDebit.numeric' => 'El monto en debe en moneda extranjera debe ser un número.',
            'lines.*.FCDebit.min' => 'El monto en debe en moneda extranjera no puede ser negativo.',

            'lines.*.FCCredit.numeric' => 'El monto en haber en moneda extranjera debe ser un número.',
            'lines.*.FCCredit.min' => 'El monto en haber en moneda extranjera no puede ser negativo.',

            'lines.*.FCCurrency.max' => 'El codigo de la moneda extranjera de la línea no debe exceder los 50 caracteres.',

            'lines.*.DueDate.date_format' => 'La fecha de vencimiento de la línea debe tener el formato YYYY-MM-DD.',
            'lines.*.LineMemo.max' => 'El comentario de la línea no debe exceder los 254 caracteres.',
            'lines.*.ReferenceDate1.date_format' => 'La fecha de referencia de la línea debe tener el formato YYYY-MM-DD.',
            'lines.*.CostingCode.max' => 'La dimensión 1 no debe exceder los 50 caracteres.',
            'lines.*.TaxDate.date_format' => 'La fecha de documento de la línea debe tener el formato YYYY-MM-DD.',
            'lines.*.CostingCode2.max' => 'La dimensión 2 no debe exceder los 50 caracteres.',
        ];
    }
}
