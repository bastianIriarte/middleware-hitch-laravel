<?php

namespace App\Http\Requests\Sap;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ArticleStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            #Tabla OITM (Artículos)
            'ItemCode'     => 'required|string|max:50',
            'ItemName'     => 'required|string|max:200',
            'ItemType'     => 'required|string|in:I,L,T,F',
            'ItmsGrpCode'  => 'required|integer',
            'UgpEntry'     => 'required|integer',
            'InvntItem'    => 'required|string|in:tYES,tNO',
            'SellItem'     => 'required|string|in:tYES,tNO',
            'PrchseItem'   => 'required|string|in:tYES,tNO',
            'ManageStockByWarehouse' => 'required|string|in:tYES,tNO',
            'SWW'          => 'required|string|max:16',
            'BuyUnitMsr'   => 'required|string|max:100',
            'SalUnitMsr'   => 'required|string|max:100',
            'PurPackUn'   =>  'nullable|integer|min:1',


            #Campos adicionales de OITM
            'U_NEGOCIO'      => 'required|string|max:50',
            'U_DEPARTAMENTO' => 'required|string|max:50',
            'U_LINEA'        => 'required|string|max:50',
            'U_CLASE'        => 'required|string|max:50',
            'U_SERIE'        => 'required|string|max:20',
            'U_CONTINUIDAD'  => 'required|string|in:S,N',
            'U_TEMPORADA'    => 'required|string|max:5',
            'U_MARCA'        => 'required|string|max:50',
            'U_COMPO'        => 'required|string|max:50',
            'U_INTEGRACION'  => 'nullable|string|in:S,N',
            'U_ANO_CREACION' => 'nullable|digits:4|numeric',
            'U_PROCEDENCIA'  => 'nullable|string|max:50',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',

            #Tabla OITW (Inventario por almacén)
            'Inventory'              => 'required|array|min:1',
            'Inventory.*.WhsCode'    => 'required|string|max:8',
            'Inventory.*.MinStock'   => 'required|numeric|min:0',
            'Inventory.*.MaxStock'   => 'required|numeric|gt:Inventory.*.MinStock',
        ];
    }

    public function messages()
    {
        return [
            // OITM
            'ItemCode.required' => 'El código del artículo es obligatorio.',
            'ItemCode.string'   => 'El código del artículo debe ser una cadena de texto.',
            'ItemCode.max'      => 'El código del artículo no debe exceder los 50 caracteres.',

            'ItemName.required' => 'El nombre del artículo es obligatorio.',
            'ItemName.string'   => 'El nombre del artículo debe ser una cadena de texto.',
            'ItemName.max'      => 'El nombre del artículo no debe exceder los 200 caracteres.',

            'ItemType.required' => 'El tipo de artículo es obligatorio.',
            'ItemType.string'   => 'El tipo de artículo debe ser una cadena de texto.',
            'ItemType.in'       => 'El tipo de artículo debe ser I (Items), L (Labor), T (Travel) o F (FixedAssets).',

            'ItmsGrpCode.required' => 'El grupo del artículo es obligatorio.',
            'ItmsGrpCode.integer'  => 'El grupo del artículo debe ser un número entero.',

            'UgpEntry.required' => 'El grupo de unidad de medida es obligatorio.',
            'UgpEntry.integer'  => 'El grupo de unidad de medida debe ser un número entero.',

            'InvntItem.required' => 'Debe indicar si el artículo es inventariable.',
            'InvntItem.string'   => 'El campo inventariable debe ser una cadena de texto.',
            'InvntItem.in'       => 'El valor de inventariable debe ser "tYES" o "tNO".',

            'SellItem.required' => 'Debe indicar si el artículo se vende.',
            'SellItem.string'   => 'El campo de venta debe ser una cadena de texto.',
            'SellItem.in'       => 'El valor de artículo para ventas debe ser "tYES" o "tNO".',

            'PrchseItem.required' => 'Debe indicar si el artículo se compra.',
            'PrchseItem.string'   => 'El campo de compra debe ser una cadena de texto.',
            'PrchseItem.in'       => 'El valor de artículo para compras debe ser "tYES" o "tNO".',

            'ManageStockByWarehouse.required' => 'Debe indicar si el artículo maneja stock por bodegas.',
            'ManageStockByWarehouse.string'   => 'El campo ManageStockByWarehouse debe ser una cadena de texto.',
            'ManageStockByWarehouse.in'       => 'El valor de ManageStockByWarehouse debe ser "tYES" o "tNO".',

            'SWW.required' => 'El SKU es obligatorio.',
            'SWW.string'   => 'El SKU debe ser una cadena de texto.',
            'SWW.max'      => 'El SKU no debe exceder los 16 caracteres.',

            'BuyUnitMsr.required' => 'La unidad de medida de compra es obligatoria.',
            'BuyUnitMsr.string'   => 'La unidad de medida de compra debe ser una cadena de texto.',
            'BuyUnitMsr.max'      => 'La unidad de medida de compra no debe exceder los 100 caracteres.',

            'SalUnitMsr.required' => 'La unidad de medida de venta es obligatoria.',
            'SalUnitMsr.string'   => 'La unidad de medida de venta debe ser una cadena de texto.',
            'SalUnitMsr.max'      => 'La unidad de medida de venta no debe exceder los 100 caracteres.',

            'PurPackUn.required' => 'El valor PurPackUn es obligatorio.',
            'PurPackUn.integer'  => 'El valor PurPackUn debe ser un número entero.',
            'PurPackUn.min'      => 'El valor PurPackUn debe ser mayor o igual a 1.',

            // Campos adicionales de OITM
            'U_NEGOCIO.required' => 'El negocio es obligatorio.',
            'U_NEGOCIO.string'   => 'El negocio debe ser una cadena de texto.',
            'U_NEGOCIO.max'      => 'El negocio no debe exceder los 50 caracteres.',

            'U_DEPARTAMENTO.required' => 'El departamento es obligatorio.',
            'U_DEPARTAMENTO.string'   => 'El departamento debe ser una cadena de texto.',
            'U_DEPARTAMENTO.max'      => 'El departamento no debe exceder los 50 caracteres.',

            'U_LINEA.required' => 'La línea es obligatoria.',
            'U_LINEA.string'   => 'La línea debe ser una cadena de texto.',
            'U_LINEA.max'      => 'La línea no debe exceder los 50 caracteres.',

            'U_CLASE.required' => 'La clase es obligatoria.',
            'U_CLASE.string'   => 'La clase debe ser una cadena de texto.',
            'U_CLASE.max'      => 'La clase no debe exceder los 50 caracteres.',

            'U_SERIE.required' => 'La serie es obligatoria.',
            'U_SERIE.string'   => 'La serie debe ser una cadena de texto.',
            'U_SERIE.max'      => 'La serie no debe exceder los 20 caracteres.',

            'U_CONTINUIDAD.required' => 'La continuidad es obligatoria.',
            'U_CONTINUIDAD.string'   => 'La continuidad debe ser una cadena de texto.',
            'U_CONTINUIDAD.in'       => 'La continuidad debe ser S o N.',

            'U_TEMPORADA.required' => 'La temporada es obligatoria.',
            'U_TEMPORADA.string'   => 'La temporada debe ser una cadena de texto.',
            'U_TEMPORADA.max'      => 'La temporada no debe exceder los 5 caracteres.',

            'U_MARCA.required' => 'La marca es obligatoria.',
            'U_MARCA.string'   => 'La marca debe ser una cadena de texto.',
            'U_MARCA.max'      => 'La marca no debe exceder los 50 caracteres.',

            'U_COMPO.required' => 'La composición es obligatoria.',
            'U_COMPO.string'   => 'La composición debe ser una cadena de texto.',
            'U_COMPO.max'      => 'La composición no debe exceder los 50 caracteres.',

            'U_INTEGRACION.required' => 'El origen del dato es obligatorio.',
            'U_INTEGRACION.string'   => 'El origen del dato debe ser una cadena de texto.',
            'U_INTEGRACION.in'       => 'El origen del dato debe ser S (Integración) o N (Manual).',

            'U_ANO_CREACION.required' => 'El año de creación es obligatorio.',
            'U_ANO_CREACION.numeric'  => 'El año de creación debe ser numérico.',
            'U_ANO_CREACION.digits'   => 'El año de creación debe tener 4 dígitos.',

            'U_PROCEDENCIA.required' => 'La procedencia es obligatoria.',
            'U_PROCEDENCIA.string'   => 'La procedencia debe ser una cadena de texto.',
            'U_PROCEDENCIA.max'      => 'La procedencia no debe exceder los 50 caracteres.',

            // OITW (Inventario)
            'Inventory.required'            => 'Debe especificar al menos un registro de inventario por almacén.',
            'Inventory.array'               => 'El inventario debe estar en formato de arreglo.',
            'Inventory.min'                 => 'Debe incluir al menos un almacén.',

            'Inventory.*.WhsCode.required'  => 'El código del almacén es obligatorio.',
            'Inventory.*.WhsCode.string'    => 'El código del almacén debe ser una cadena de texto.',
            'Inventory.*.WhsCode.max'       => 'El código del almacén no debe exceder los 8 caracteres.',

            'Inventory.*.MinStock.required' => 'El stock mínimo es obligatorio.',
            'Inventory.*.MinStock.numeric'  => 'El stock mínimo debe ser un número.',
            'Inventory.*.MinStock.min'      => 'El stock mínimo no puede ser negativo para el almacén con código :whs.',

            'Inventory.*.MaxStock.required' => 'El stock máximo es obligatorio.',
            'Inventory.*.MaxStock.numeric'  => 'El stock máximo debe ser un número.',
            'Inventory.*.MaxStock.gt'       => 'El stock máximo debe ser mayor que el stock mínimo para el almacén con código :whs.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $key => $messages) {
            foreach ($messages as $message) {
                $errors[] = $this->replaceDynamicPlaceholders($key, $message);
            }
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'data'    => implode(' | ', $errors),
            'timestamp' => now()->toISOString()
        ], 422));
    }

    private function replaceDynamicPlaceholders(string $key, string $message): string
    {
        if (preg_match('/^Inventory\.(\d+)\./', $key, $matches)) {
            $index = (int)$matches[1];
            $whsCode = $this->input("Inventory.$index.WhsCode");
            if ($whsCode) {
                $message = str_replace(':whs', $whsCode, $message);
            }
        }
        return $message;
    }
}
