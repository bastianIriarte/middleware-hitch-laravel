<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class ReturnStoreRequest extends FormRequest
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
            // Encabezado (ORPD)
            'CardCode'    => 'required|string|max:15',
            'CardName'    => 'required|string|max:100',
            'DocDate'     => 'required|date_format:Y-m-d',
            'DocDueDate'  => 'required|date_format:Y-m-d',
            'TaxDate'     => 'required|date_format:Y-m-d',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',
            // Detalle (RPD1)
            'lines'                  => 'required|array|min:1',
            'lines.*.ItemCode'       => 'required|string|max:20',
            'lines.*.Quantity'       => 'required|numeric|min:0.000001',
            'lines.*.TaxCode'        => 'nullable|string|max:10',
            'lines.*.UnitPrice'      => 'required|numeric|min:0',
            'lines.*.WhsCode'        => 'required|string|max:8',
            'lines.*.OcrCode'        => 'nullable|string|max:10',
            'lines.*.OcrCode2'       => 'nullable|string|max:10',
            'lines.*.OcrCode3'       => 'nullable|string|max:10',
            'lines.*.BaseType'       => 'required|integer|in:20',
            'lines.*.BaseEntry'      => 'required|integer',
            'lines.*.BaseLine'       => 'required|integer',
        ];
    }

    public function messages()
    {
        return [
            'CardCode.required' => 'El código del socio de negocio es obligatorio.',
            'CardCode.max'      => 'El código del socio no debe exceder los 15 caracteres.',

            'CardName.required' => 'El nombre del socio de negocio es obligatorio.',
            'CardName.max'      => 'El nombre del socio no debe exceder los 100 caracteres.',

            'DocDate.required'    => 'La fecha de contabilización es obligatoria.',
            'DocDate.date_format' => 'La fecha de contabilización debe tener el formato YYYY-MM-DD.',

            'DocDueDate.required'    => 'La fecha de vencimiento es obligatoria.',
            'DocDueDate.date_format' => 'La fecha de vencimiento debe tener el formato YYYY-MM-DD.',

            'TaxDate.required'    => 'La fecha del documento es obligatoria.',
            'TaxDate.date_format' => 'La fecha del documento debe tener el formato YYYY-MM-DD.',

            'lines.required' => 'Debe incluir al menos una línea de detalle.',
            'lines.array'    => 'El campo de líneas debe ser un arreglo.',

            'lines.*.ItemCode.required' => 'El código del artículo es obligatorio.',
            'lines.*.ItemCode.max'      => 'El código del artículo no debe exceder los 20 caracteres.',

            'lines.*.Quantity.required' => 'La cantidad es obligatoria.',
            'lines.*.Quantity.numeric'  => 'La cantidad debe ser numérica.',
            'lines.*.Quantity.min'      => 'La cantidad debe ser mayor que cero.',

            'lines.*.UnitPrice.required' => 'El precio del artículo es obligatorio.',
            'lines.*.UnitPrice.numeric'  => 'El precio del artículo debe ser numérico.',
            'lines.*.UnitPrice.min'      => 'El precio no puede ser negativo.',

            'lines.*.WhsCode.required' => 'El código del almacén es obligatorio.',
            'lines.*.WhsCode.max'      => 'El código del almacén no debe exceder los 8 caracteres.',

            'lines.*.BaseType.required' => 'El tipo de documento base es obligatorio.',
            'lines.*.BaseType.in'       => 'El tipo de documento base debe ser 20 (Entrada de mercadería).',

            'lines.*.BaseEntry.required' => 'El número del documento base es obligatorio.',
            'lines.*.BaseEntry.integer'  => 'El número del documento base debe ser un número entero.',

            'lines.*.BaseLine.required' => 'El número de línea base es obligatorio.',
            'lines.*.BaseLine.integer'  => 'El número de línea base debe ser un número entero.',
        ];
    }
}
