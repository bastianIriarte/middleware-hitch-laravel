<?php

namespace App\Http\Requests\Sap;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PurchaseOrderStoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            // Encabezado OPOR
            'CardCode'     => 'required|string|max:15',
            'DocDate'      => 'required|date_format:Y-m-d',
            'DocDueDate'   => 'required|date_format:Y-m-d',
            'NumAtCard'    => 'required|string|max:200',
            'DocCurrency'    => 'nullable|string|max:3',
            'U_ENCARGADO_COMPRA' => 'nullable|string|max:50',
            'U_INVOICE'    => 'nullable|string|max:50',
            'U_BL'         => 'nullable|string|max:50',
            'U_ETD'        => 'nullable|date_format:Y-m-d',
            'U_ETA'        => 'nullable|date_format:Y-m-d',
            'U_PEMBARQUE'  => 'nullable|string|max:50',
            'U_PDESTINO'   => 'nullable|string|max:50',
            'U_PCONSOLID'  => 'nullable|string|max:50',
            'U_NSALIDA'    => 'nullable|string|max:50',
            'U_NLLEGADA'   => 'nullable|string|max:50',
            'U_FORWARDER'  => 'nullable|string|max:50',
            'U_AGENCIA'    => 'nullable|string|max:50',
            'CONTENEDOR'   => 'nullable|array',
            'CONTENEDOR.*.U_CONTENEDOR'   => 'nullable|string|max:50|required_without:CONTENEDOR.*.U_SELLO',
            'CONTENEDOR.*.U_SELLO'        => 'nullable|string|max:50|required_without:CONTENEDOR.*.U_CONTENEDOR',
            'U_INTEGRACION' => 'required|string|in:S,N',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',
            // Validación condicional de InvoiceData
            'CreateInvoice' => 'nullable|string|in:tYES,tNO',

            'InvoiceData' => 'required_if:CreateInvoice,tYES|array',
            'InvoiceData.DocDate'   => 'required_if:CreateInvoice,tYES|date_format:Y-m-d',
            'InvoiceData.DocDueDate' => 'required_if:CreateInvoice,tYES|date_format:Y-m-d',
            'InvoiceData.FolioPref' => 'required_if:CreateInvoice,tYES|string|max:10',
            'InvoiceData.FolioNum'  => 'required_if:CreateInvoice,tYES|integer|min:1',
            'InvoiceData.U_Integracion' => 'required_if:CreateInvoice,tYES|string|in:S,N',
            'InvoiceData.U_Status'  => 'required_if:CreateInvoice,tYES|string|in:Enviar,No enviar',
            'InvoiceData.U_SEI_CARPETA' => 'nullable|string|max:20',


            // Detalle POR1
            'lines' => 'required|array|min:1',
            'lines.*.ItemCode'     => 'required|string|max:50',
            'lines.*.U_COMPO_FINAL' => 'nullable|string|max:50',
            'lines.*.Quantity'     => 'required|numeric|min:0.000001',
            'lines.*.Price'        => 'required|numeric|min:0',
            'lines.*.Currency'       => 'nullable|string|max:3',
            'lines.*.TaxCode'      => 'nullable|string|max:8',
            'lines.*.WhsCode'      => 'nullable|string|max:8',
            'lines.*.OcrCode'      => 'nullable|string|max:8',
            'lines.*.OcrCode2'     => 'nullable|string|max:8',
            'lines.*.OcrCode3'     => 'nullable|string|max:8',
            'lines.*.BaseType'     => 'nullable|integer',
            'lines.*.BaseEntry'    => 'nullable|integer',
            'lines.*.BaseLine'     => 'nullable|integer',
            'lines.*.U_SEI_Aprobador' => 'nullable|string|max:20',
            'lines.*.U_Integracion' => 'nullable|string|in:S,N',
            'lines.*.U_Status'     => 'nullable|string|in:Enviar,No enviar',
        ];
    }

    public function messages()
    {
        return [
            // Encabezado OPOR
            'CardCode.required' => 'El código del proveedor es obligatorio.',
            'CardCode.string'   => 'El código del proveedor debe ser una cadena de texto.',
            'CardCode.max'      => 'El código del proveedor no debe exceder los 15 caracteres.',

            'DocDate.required'  => 'La fecha de contabilización es obligatoria.',
            'DocDate.date_format' => 'La fecha de contabilización debe tener el formato YYYY-MM-DD.',

            'DocDueDate.required' => 'La fecha de entrega es obligatoria.',
            'DocDueDate.date_format' => 'La fecha de entrega debe tener el formato YYYY-MM-DD.',

            'NumAtCard.required' => 'La referencia (folio FMMS) es obligatoria.',
            'NumAtCard.string'   => 'La referencia debe ser una cadena de texto.',
            'NumAtCard.max'      => 'La referencia no debe exceder los 200 caracteres.',

            // 'U_ENCARGADO_COMPRA.required' => 'El encargado de compras es obligatorio.',
            'U_ENCARGADO_COMPRA.string'   => 'El encargado de compras debe ser una cadena de texto.',
            'U_ENCARGADO_COMPRA.max'      => 'El encargado de compras no debe exceder los 50 caracteres.',

            // 'U_INVOICE.required' => 'El número de invoice es obligatorio.',
            'U_INVOICE.string'   => 'El número de invoice debe ser una cadena de texto.',
            'U_INVOICE.max'      => 'El número de invoice no debe exceder los 50 caracteres.',

            // 'U_BL.required' => 'El número de BL es obligatorio.',
            'U_BL.string'   => 'El número de BL debe ser una cadena de texto.',
            'U_BL.max'      => 'El número de BL no debe exceder los 50 caracteres.',

            // 'U_ETD.required' => 'La fecha estimada de salida (ETD) es obligatoria.',
            'U_ETD.date_format' => 'La fecha ETD debe tener el formato YYYY-MM-DD.',

            // 'U_ETA.required' => 'La fecha estimada de arribo (ETA) es obligatoria.',
            'U_ETA.date_format' => 'La fecha ETA debe tener el formato YYYY-MM-DD.',

            // 'U_PEMBARQUE.required' => 'El puerto de embarque es obligatorio.',
            'U_PEMBARQUE.string'   => 'El puerto de embarque debe ser una cadena de texto.',
            'U_PEMBARQUE.max'      => 'El puerto de embarque no debe exceder los 50 caracteres.',

            // 'U_PDESTINO.required' => 'El puerto de destino es obligatorio.',
            'U_PDESTINO.string'   => 'El puerto de destino debe ser una cadena de texto.',
            'U_PDESTINO.max'      => 'El puerto de destino no debe exceder los 50 caracteres.',

            'U_PCONSOLID.string'  => 'El puerto de consolidación debe ser una cadena de texto.',
            'U_PCONSOLID.max'     => 'El puerto de consolidación no debe exceder los 50 caracteres.',

            // 'U_NSALIDA.required' => 'La nave de salida es obligatoria.',
            'U_NSALIDA.string'   => 'La nave de salida debe ser una cadena de texto.',
            'U_NSALIDA.max'      => 'La nave de salida no debe exceder los 50 caracteres.',

            // 'U_NLLEGADA.required' => 'La nave de llegada es obligatoria.',
            'U_NLLEGADA.string'   => 'La nave de llegada debe ser una cadena de texto.',
            'U_NLLEGADA.max'      => 'La nave de llegada no debe exceder los 50 caracteres.',

            // 'U_FORWARDER.required' => 'El forwarder es obligatorio.',
            'U_FORWARDER.string'   => 'El forwarder debe ser una cadena de texto.',
            'U_FORWARDER.max'      => 'El forwarder no debe exceder los 50 caracteres.',

            'U_AGENCIA.string' => 'La agencia de aduanas debe ser una cadena de texto.',
            'U_AGENCIA.max'    => 'La agencia de aduanas no debe exceder los 50 caracteres.',

            'CONTENEDOR.array'                       => 'El campo CONTENEDOR debe ser un arreglo de objetos.',
            'CONTENEDOR.min'                         => 'Si envías CONTENEDOR, debe contener al menos un elemento.',
            'CONTENEDOR.*.U_CONTENEDOR.required_without' => 'Cada objeto de CONTENEDOR debe incluir U_CONTENEDOR o U_SELLO.',
            'CONTENEDOR.*.U_SELLO.required_without'     => 'Cada objeto de CONTENEDOR debe incluir U_SELLO o U_CONTENEDOR.',
            'CONTENEDOR.*.U_CONTENEDOR.string'       => 'U_CONTENEDOR debe ser una cadena de texto.',
            'CONTENEDOR.*.U_CONTENEDOR.max'          => 'U_CONTENEDOR no debe exceder los 50 caracteres.',
            'CONTENEDOR.*.U_SELLO.string'            => 'U_SELLO debe ser una cadena de texto.',
            'CONTENEDOR.*.U_SELLO.max'               => 'U_SELLO no debe exceder los 50 caracteres.',

            'U_INTEGRACION.required' => 'El origen del dato es obligatorio.',
            'U_INTEGRACION.string'   => 'El origen del dato debe ser una cadena de texto.',
            'U_INTEGRACION.in'       => 'El origen del dato debe ser S (Integración) o N (Manual).',

            // Detalle POR1
            'lines.required' => 'Debe incluir al menos una línea de detalle.',
            'lines.array'    => 'El detalle debe estar en formato de lista.',

            'lines.*.ItemCode.required' => 'El código del artículo es obligatorio.',
            'lines.*.ItemCode.string'   => 'El código del artículo debe ser una cadena de texto.',
            'lines.*.ItemCode.max'      => 'El código del artículo no debe exceder los 50 caracteres.',

            'lines.*.OcrCode.string'   => 'El código del Centro de costo debe ser una cadena de texto.',
            'lines.*.OcrCode.max'      => 'El código del Centro de costo no debe exceder los 8 caracteres.',

            'lines.*.OcrCode2.string'   => 'El código del Proyecto debe ser una cadena de texto.',
            'lines.*.OcrCode2.max'      => 'El código del Proyecto no debe exceder los 8 caracteres.',

            'lines.*.OcrCode3.string'   => 'El código de Actividad debe ser una cadena de texto.',
            'lines.*.OcrCode3.max'      => 'El código de Actividad no debe exceder los 8 caracteres.',

            'lines.*.U_COMPO_FINAL.required' => 'La composición final es obligatoria.',
            'lines.*.U_COMPO_FINAL.string'   => 'La composición final debe ser una cadena de texto.',
            'lines.*.U_COMPO_FINAL.max'      => 'La composición final no debe exceder los 50 caracteres.',

            'lines.*.Quantity.required' => 'La cantidad es obligatoria.',
            'lines.*.Quantity.numeric'  => 'La cantidad debe ser un número.',
            'lines.*.Quantity.min'      => 'La cantidad debe ser mayor que cero.',

            'lines.*.Price.required' => 'El precio unitario es obligatorio.',
            'lines.*.Price.numeric'  => 'El precio unitario debe ser un número.',
            'lines.*.Price.min'      => 'El precio unitario no puede ser negativo.',

            'lines.*.U_Integracion.required' => 'El origen de la línea es obligatorio.',
            'lines.*.U_Integracion.in'       => 'El origen de la línea debe ser S (Integración) o N (Manual).',

            'lines.*.U_Status.required' => 'El estado de envío de la línea es obligatorio.',
            'lines.*.U_Status.in'       => 'El estado de envío debe ser "Enviar" o "No enviar".',

            // CreateInvoice
            'CreateInvoice.in' => 'El campo CreateInvoice solo puede ser tYES o tNO.',

            // InvoiceData
            'InvoiceData.required_if' => 'Debe enviar los datos de la factura cuando CreateInvoice es tYES.',
            'InvoiceData.array'       => 'Los datos de la factura deben estar en formato de objeto.',

            'InvoiceData.DocDate.required_if' => 'La fecha de contabilización de la factura es obligatoria cuando CreateInvoice es tYES.',
            'InvoiceData.DocDate.date_format' => 'La fecha de la factura debe tener el formato YYYY-MM-DD.',

            'InvoiceData.DocDueDate.required_if' => 'La fecha de vencimiento de la factura es obligatoria cuando CreateInvoice es tYES.',
            'InvoiceData.DocDueDate.date_format' => 'La fecha de vencimiento debe tener el formato YYYY-MM-DD.',

            'InvoiceData.FolioPref.required_if' => 'El prefijo del folio de la factura es obligatorio cuando CreateInvoice es tYES.',
            'InvoiceData.FolioPref.string'      => 'El prefijo del folio debe ser una cadena de texto.',
            'InvoiceData.FolioPref.max'         => 'El prefijo del folio no debe exceder los 10 caracteres.',

            'InvoiceData.FolioNum.required_if' => 'El número de folio de la factura es obligatorio cuando CreateInvoice es tYES.',
            'InvoiceData.FolioNum.integer'     => 'El número de folio debe ser un valor numérico.',
            'InvoiceData.FolioNum.min'         => 'El número de folio debe ser mayor a 0.',

            'InvoiceData.U_Integracion.required_if' => 'El origen de la factura es obligatorio cuando CreateInvoice es tYES.',
            'InvoiceData.U_Integracion.in'          => 'El origen de la factura debe ser S (Integración) o N (Manual).',

            'InvoiceData.U_Status.required_if' => 'El estado de la factura es obligatorio cuando CreateInvoice es tYES.',
            'InvoiceData.U_Status.in'          => 'El estado de la factura debe ser "Enviar" o "No enviar".',

            'InvoiceData.U_SEI_CARPETA.string' => 'La carpeta de la factura debe ser una cadena de texto.',
            'InvoiceData.U_SEI_CARPETA.max'    => 'La carpeta de la factura no debe exceder los 20 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $key => $messages) {
            foreach ($messages as $message) {
                $errors[] = $message;
            }
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'data'    => implode(' | ', $errors),
            'timestamp' => now()->toISOString()
        ], 422));
    }
}
