<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class GoodsIssueStoreRequest extends FormRequest
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
            // Encabezado (OIGE)
            'DocDate'      => 'required|date_format:Y-m-d',
            'DocDueDate'   => 'required|date_format:Y-m-d',
            'TaxDate'      => 'required|date_format:Y-m-d',
            'Reference2'     => 'sometimes|string|max:255',
            'Comments'     => 'sometimes|string|max:255',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',

            // Detalle (PDN1)
            'lines'                  => 'required|array|min:1',
            'lines.*.ItemCode'       => 'required|string|max:20',
            'lines.*.Quantity'       => 'required|numeric|min:0.000001',
            // 'lines.*.TaxCode'        => 'nullable|string|max:10',
            'lines.*.Price'      => 'required|numeric|min:0',
            'lines.*.WhsCode'        => 'required|string|max:8',
            'lines.*.CostingCode'        => 'nullable|string|max:50',
            'lines.*.CostingCode2'       => 'nullable|string|max:50',
            'lines.*.AccountCode'       => 'nullable|string|max:50'
        ];
    }

    public function messages()
    {
        return [
            'DocDate.required'     => 'La fecha de contabilización es obligatoria.',
            'DocDate.date_format'  => 'La fecha de contabilización debe tener el formato YYYY-MM-DD.',

            'DocDueDate.required'     => 'La fecha de vencimiento es obligatoria.',
            'DocDueDate.date_format'  => 'La fecha de vencimiento debe tener el formato YYYY-MM-DD.',

            'Reference2.string'   => 'El campo Referencia 2 debe ser un texto.',
            'Reference2.max'      => 'El campo Referencia 2 no debe exceder los 255 caracteres.',

            'Comments.string'   => 'El campo Referencia 2 debe ser un texto.',
            'Comments.max'      => 'El campo Referencia 2 no debe exceder los 255 caracteres.',

            'TaxDate.required'     => 'La fecha del documento es obligatoria.',
            'TaxDate.date_format'  => 'La fecha del documento debe tener el formato YYYY-MM-DD.',

            'lines.required'       => 'Debe incluir al menos una línea de detalle.',
            'lines.array'          => 'El campo de líneas debe ser un arreglo.',
            'lines.min'            => 'Debe incluir al menos una línea de detalle.',

            'lines.*.ItemCode.required' => 'El código del artículo es obligatorio.',
            'lines.*.ItemCode.max'      => 'El código del artículo no debe exceder los 20 caracteres.',

            'lines.*.Quantity.required' => 'La cantidad es obligatoria.',
            'lines.*.Quantity.numeric'  => 'La cantidad debe ser un número.',
            'lines.*.Quantity.min'      => 'La cantidad debe ser mayor que cero.',

            'lines.*.Price.required' => 'El precio es obligatorio.',
            'lines.*.Price.numeric'  => 'El precio debe ser numérico.',
            'lines.*.Price.min'      => 'El precio no puede ser negativo.',

            'lines.*.WhsCode.required' => 'El código del almacén es obligatorio.',
            'lines.*.WhsCode.max'      => 'El código del almacén no debe exceder los 8 caracteres.',

            'lines.*.CostingCode.max'  => 'El código de sucursal no debe exceder los 50 caracteres.',
            'lines.*.CostingCode2.max' => 'El código de area no debe exceder los 50 caracteres.',
            'lines.*.AccountCode.max'  => 'El código de cuenta contable no debe exceder los 50 caracteres.',
        ];
    }
}
