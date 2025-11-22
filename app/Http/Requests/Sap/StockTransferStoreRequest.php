<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferStoreRequest extends FormRequest
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
            // Encabezado (OWTR)
            'CardCode'     => 'required|string|max:15',
            'DocDate'      => 'required|date_format:Y-m-d',
            'TaxDate'      => 'required|date_format:Y-m-d',
            'Filler'       => 'required|string|max:8',
            'ToWhsCode'    => 'required|string|max:8',
            'Series'       => 'required|numeric',
            'U_Integracion'     => 'required|string|in:S,N',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',
            // UDFs según tabla
            'U_BFE_TipoDoctoRef'   => 'nullable|string|max:50',
            'U_BFE_FechaRef'       => 'nullable|string',
            'U_BFE_IndTraslado'    => 'nullable|string|max:50',
            'U_BFE_RutChofer'      => 'nullable|string|max:10',
            'U_BFE_NombreChofer'   => 'nullable|string|max:30',
            'U_BFE_RutTrasporte'  => 'nullable|string|max:10',
            'U_BFE_Patente'        => 'nullable|string|max:8',

            // Detalle (WTR1)
            'lines'                  => 'required|array|min:1',
            'lines.*.ItemCode'       => 'required|string|max:20',
            'lines.*.Quantity'       => 'required|numeric|min:0.000001',
            'lines.*.Price'          => 'required|numeric|min:0',
            'lines.*.WhsCode'        => 'sometimes|string|max:8'
        ];
    }

    public function messages()
    {
        return [
            // Encabezado
            'CardCode.required'     => 'El código del socio de negocio es obligatorio.',
            'CardCode.string'       => 'El código del socio de negocio debe ser texto.',
            'CardCode.max'          => 'El código del socio de negocio no debe exceder 15 caracteres.',

            'DocDate.required'      => 'La fecha de contabilización es obligatoria.',
            'DocDate.date_format'   => 'La fecha de contabilización debe tener el formato YYYY-MM-DD.',

            'TaxDate.required'      => 'La fecha del documento es obligatoria.',
            'TaxDate.date_format'   => 'La fecha del documento debe tener el formato YYYY-MM-DD.',

            'Filler.required'       => 'El almacén de origen es obligatorio.',
            'Filler.string'         => 'El almacén de origen debe ser texto.',
            'Filler.max'            => 'El almacén de origen no debe exceder 8 caracteres.',

            'ToWhsCode.required'    => 'El almacén de destino es obligatorio.',
            'ToWhsCode.string'      => 'El almacén de destino debe ser texto.',
            'ToWhsCode.max'         => 'El almacén de destino no debe exceder 8 caracteres.',

            'Series.Quantity.required' => 'La Serie es obligatoria.',
            'Series.Quantity.numeric'  => 'La Serie debe ser un número.',

            'U_Integracion.required' => 'El campo de integración es obligatorio.',
            'U_Integracion.string'  => 'El campo de integración debe ser texto.',
            'U_Integracion.in'      => 'El campo de integración debe ser "S" o "N".',

            // UDFs
            'U_BFE_TipoDoctoRef.max'    => 'El campo Tipo Documento Ref no debe exceder 50 caracteres.',
            'U_BFE_FechaRef.date'       => 'La Fecha Ref debe ser una fecha válida.',
            'U_BFE_IndTraslado.max'     => 'El campo Ind Traslado no debe exceder 50 caracteres.',
            'U_BFE_NombreChofer.max'    => 'El Nombre del Chofer no debe exceder 30 caracteres.',
            'U_BFE_Patente.max'         => 'La Patente no debe exceder 8 caracteres.',

            // Detalle
            'lines.required'        => 'Debe incluir al menos una línea de detalle.',
            'lines.array'           => 'El campo de líneas debe ser un arreglo.',
            'lines.min'             => 'Debe incluir al menos una línea de detalle.',
            'lines.max'             => 'No puede incluir más de 24 líneas en el documento.',

            'lines.*.ItemCode.required' => 'El código del artículo es obligatorio.',
            'lines.*.ItemCode.string'   => 'El código del artículo debe ser texto.',
            'lines.*.ItemCode.max'      => 'El código del artículo no debe exceder 20 caracteres.',

            'lines.*.Quantity.required' => 'La cantidad es obligatoria.',
            'lines.*.Quantity.numeric'  => 'La cantidad debe ser un número.',
            'lines.*.Quantity.min'      => 'La cantidad debe ser mayor a cero.',

            'lines.*.WhsCode.string'    => 'El almacén debe ser texto.',
            'lines.*.WhsCode.max'       => 'El almacén no debe exceder 8 caracteres.',
        ];
    }
}
