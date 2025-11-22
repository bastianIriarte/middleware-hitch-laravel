<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class ReserveInvoiceStoreRequest extends FormRequest
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
            // Encabezado (OPCH)
            'CardCode'     => 'required|string|max:15',
            // 'CardName'     => 'required|string|max:100',
            'DocDate'      => 'required|date_format:Y-m-d',
            'DocDueDate'   => 'required|date_format:Y-m-d',
            'TaxDate'      => 'required|date_format:Y-m-d',
            'FolioPref'    => 'nullable|string|max:4',
            'DocCurrency'    => 'nullable|string|max:3',
            'FolioNum'     => 'nullable|integer',
            'U_INTEGRACION' => 'required|string|in:S,N',
            'U_STATUS'     => 'nullable|string|in:Enviar,No enviar',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',
            // Detalle (PCH1)
            'lines' => 'required|array|min:1',
            'lines.*.ItemCode'       => 'required|string|max:50',
            'lines.*.Quantity'       => 'required|numeric|min:0.000001',
            'lines.*.Price'          => 'required|numeric|min:0',
            'lines.*.Currency'       => 'nullable|string|max:3',
            'lines.*.TaxCode'        => 'nullable|string|max:8',
            'lines.*.WhsCode'        => 'nullable|string|max:8',
            'lines.*.OcrCode'        => 'nullable|string|max:8',
            'lines.*.OcrCode2'       => 'nullable|string|max:8',
            'lines.*.OcrCode3'       => 'nullable|string|max:8',
            'lines.*.BaseType'       => 'nullable|integer',
            'lines.*.BaseEntry'      => 'nullable|integer',
            'lines.*.BaseLine'       => 'nullable|integer',
            'lines.*.U_SEI_CARPETA'  => 'nullable|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            // Encabezado
            'CardCode.required' => 'El código del socio de negocio es obligatorio.',
            'CardCode.max' => 'El código del socio de negocio no debe exceder los 15 caracteres.',

            // 'CardName.required' => 'El nombre del socio de negocio es obligatorio.',
            // 'CardName.max' => 'El nombre del socio no debe exceder los 100 caracteres.',

            'DocDate.required' => 'La fecha de contabilización es obligatoria.',
            'DocDate.date_format' => 'La fecha de contabilización debe tener el formato YYYY-MM-DD.',

            'DocDueDate.required' => 'La fecha de vencimiento es obligatoria.',
            'DocDueDate.date_format' => 'La fecha de vencimiento debe tener el formato YYYY-MM-DD.',

            'TaxDate.required' => 'La fecha del documento es obligatoria.',
            'TaxDate.date_format' => 'La fecha del documento debe tener el formato YYYY-MM-DD.',

            'FolioPref.required' => 'El prefijo del folio es obligatorio.',
            'FolioPref.max' => 'El prefijo del folio no debe exceder los 4 caracteres.',

            'FolioNum.required' => 'El número de folio es obligatorio.',
            'FolioNum.integer' => 'El número de folio debe ser un número entero.',

            'U_INTEGRACION.required' => 'El origen del documento es obligatorio.',
            'U_INTEGRACION.in' => 'El origen del dato debe ser S (Integración) o N (Manual).',

            'U_STATUS.required' => 'El estado del documento es obligatorio.',
            'U_STATUS.in' => 'El estado debe ser "Enviar" o "No enviar".',

            // Detalle
            'lines.required' => 'Debe incluir al menos una línea en la factura.',
            'lines.array' => 'El campo "lines" debe ser un arreglo.',

            'lines.*.ItemCode.required' => 'El código del artículo es obligatorio.',
            'lines.*.ItemCode.max' => 'El código del artículo no debe exceder los 50 caracteres.',

            'lines.*.Quantity.required' => 'La cantidad es obligatoria.',
            'lines.*.Quantity.numeric' => 'La cantidad debe ser un número.',
            'lines.*.Quantity.min' => 'La cantidad debe ser mayor que cero.',

            'lines.*.Price.required' => 'El precio unitario es obligatorio.',
            'lines.*.Price.numeric' => 'El precio unitario debe ser un número.',
            'lines.*.Price.min' => 'El precio unitario no puede ser negativo.',

            'lines.*.OcrCode.string'   => 'El código del Centro de costo debe ser una cadena de texto.',
            'lines.*.OcrCode.max'      => 'El código del Centro de costo no debe exceder los 8 caracteres.',

            'lines.*.OcrCode2.string'   => 'El código del Proyecto debe ser una cadena de texto.',
            'lines.*.OcrCode2.max'      => 'El código del Proyecto no debe exceder los 8 caracteres.',

            'lines.*.OcrCode3.string'   => 'El código de Actividad debe ser una cadena de texto.',
            'lines.*.OcrCode3.max'      => 'El código de Actividad no debe exceder los 8 caracteres.',

            'lines.*.BaseType.required' => 'El tipo de documento base es obligatorio.',
            'lines.*.BaseType.integer' => 'El tipo de documento base debe ser un número.',

            'lines.*.BaseEntry.required' => 'El número del documento base es obligatorio.',
            'lines.*.BaseEntry.integer' => 'El número del documento base debe ser un número.',

            'lines.*.BaseLine.required' => 'El número de línea base es obligatorio.',
            'lines.*.BaseLine.integer' => 'El número de línea base debe ser un número.',

            'lines.*.U_SEI_CARPETA.required' => 'El número de carpeta de importación es obligatorio.',
            'lines.*.U_SEI_CARPETA.max' => 'El número de carpeta no debe exceder los 20 caracteres.',
        ];
    }
}
