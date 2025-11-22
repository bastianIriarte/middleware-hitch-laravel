<?php

namespace App\Http\Requests\Sap;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ArticleUpdateRequest extends FormRequest
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
            'ItemCode'     => 'required|string|max:50',
            'ItemName'     => 'sometimes|string|max:200',
            'ItemType'     => 'sometimes|string|in:I,L,T,F',
            'ItmsGrpCode'  => 'sometimes|integer',
            'UgpEntry'     => 'sometimes|integer',
            'InvntItem'    => 'sometimes|string|in:tYES,tNO',
            'SellItem'     => 'sometimes|string|in:tYES,tNO',
            'PrchseItem'   => 'sometimes|string|in:tYES,tNO',
            'ManageStockByWarehouse' => 'sometimes|string|in:tYES,tNO',
            'SWW'          => 'sometimes|string|max:16',
            'BuyUnitMsr'   => 'sometimes|string|max:100',
            'SalUnitMsr'   => 'sometimes|string|max:100',
            'PurPackUn'   =>  'nullable|integer|min:1',

            // Campos adicionales OITM
            'U_NEGOCIO'      => 'sometimes|string|max:50',
            'U_DEPARTAMENTO' => 'sometimes|string|max:50',
            'U_LINEA'        => 'sometimes|string|max:50',
            'U_CLASE'        => 'sometimes|string|max:50',
            'U_SERIE'        => 'sometimes|string|max:20',
            'U_CONTINUIDAD'  => 'sometimes|string|in:S,N',
            'U_TEMPORADA'    => 'sometimes|string|max:5',
            'U_MARCA'        => 'sometimes|string|max:50',
            'U_COMPO'        => 'sometimes|string|max:50',
            'U_INTEGRACION'  => 'sometimes|string|in:S,N',
            'U_ANO_CREACION' => 'sometimes|digits:4|numeric',
            'U_PROCEDENCIA'  => 'sometimes|string|max:50',
            'ORIGEN_PETICION'  => 'nullable|string|max:50',

            // Inventario (OITW)
            'Inventory'                => 'sometimes|array|min:1',
            'Inventory.*.WhsCode'      => 'required_with:Inventory|string|max:8',
            'Inventory.*.MinStock'     => 'required_with:Inventory|numeric|min:0',
            'Inventory.*.MaxStock'     => 'required_with:Inventory|numeric|gt:Inventory.*.MinStock',
        ];
    }

    public function messages()
    {
        return [
            // OITM
            'ItemCode.required' => 'El código del artículo es obligatorio.',
            'ItemCode.string'   => 'El código del artículo debe ser una cadena de texto.',
            'ItemCode.max'      => 'El código del artículo no debe exceder los 50 caracteres.',

            'ItemName.string'   => 'El nombre del artículo debe ser una cadena de texto.',
            'ItemName.max'      => 'El nombre del artículo no debe exceder los 200 caracteres.',

            'ItemType.string'   => 'El tipo de artículo debe ser una cadena de texto.',
            'ItemType.in'       => 'El tipo de artículo debe ser I (Items), L (Labor), T (Travel) o F (FixedAssets).',

            'ItmsGrpCode.integer' => 'El grupo del artículo debe ser un número entero.',
            'UgpEntry.integer'    => 'El grupo de unidad de medida debe ser un número entero.',

            'InvntItem.in'        => 'El valor de inventariable debe ser "tYES" o "tNO".',
            'SellItem.in'         => 'El valor de artículo para ventas debe ser "tYES" o "tNO".',
            'PrchseItem.in'       => 'El valor de artículo para compras debe ser "tYES" o "tNO".',
            'ManageStockByWarehouse.in' => 'El valor de ManageStockByWarehouse debe ser "tYES" o "tNO".',

            'SWW.string' => 'El SKU debe ser una cadena de texto.',
            'SWW.max'    => 'El SKU no debe exceder los 16 caracteres.',

            'BuyUnitMsr.string' => 'La unidad de medida de compra debe ser una cadena de texto.',
            'BuyUnitMsr.max'    => 'La unidad de medida de compra no debe exceder los 100 caracteres.',

            'SalUnitMsr.string' => 'La unidad de medida de venta debe ser una cadena de texto.',
            'SalUnitMsr.max'    => 'La unidad de medida de venta no debe exceder los 100 caracteres.',

            'PurPackUn.required' => 'El valor PurPackUn es obligatorio.',
            'PurPackUn.integer'  => 'El valor PurPackUn debe ser un número entero.',
            'PurPackUn.min'      => 'El valor PurPackUn debe ser mayor o igual a 1.',


            // Campos adicionales OITM
            'U_NEGOCIO.string' => 'El negocio debe ser una cadena de texto.',
            'U_NEGOCIO.max'    => 'El negocio no debe exceder los 50 caracteres.',

            'U_DEPARTAMENTO.string' => 'El departamento debe ser una cadena de texto.',
            'U_DEPARTAMENTO.max'    => 'El departamento no debe exceder los 50 caracteres.',

            'U_LINEA.string' => 'La línea debe ser una cadena de texto.',
            'U_LINEA.max'    => 'La línea no debe exceder los 50 caracteres.',

            'U_CLASE.string' => 'La clase debe ser una cadena de texto.',
            'U_CLASE.max'    => 'La clase no debe exceder los 50 caracteres.',

            'U_SERIE.string' => 'La serie debe ser una cadena de texto.',
            'U_SERIE.max'    => 'La serie no debe exceder los 20 caracteres.',

            'U_CONTINUIDAD.in' => 'La continuidad debe ser S o N.',

            'U_TEMPORADA.string' => 'La temporada debe ser una cadena de texto.',
            'U_TEMPORADA.max'    => 'La temporada no debe exceder los 5 caracteres.',

            'U_MARCA.string' => 'La marca debe ser una cadena de texto.',
            'U_MARCA.max'    => 'La marca no debe exceder los 50 caracteres.',

            'U_COMPO.string' => 'La composición debe ser una cadena de texto.',
            'U_COMPO.max'    => 'La composición no debe exceder los 50 caracteres.',

            'U_INTEGRACION.in' => 'El origen del dato debe ser S (Integración) o N (Manual).',

            'U_ANO_CREACION.numeric' => 'El año de creación debe ser numérico.',
            'U_ANO_CREACION.digits'  => 'El año de creación debe tener 4 dígitos.',

            'U_PROCEDENCIA.string' => 'La procedencia debe ser una cadena de texto.',
            'U_PROCEDENCIA.max'    => 'La procedencia no debe exceder los 50 caracteres.',

            // Inventario
            'Inventory.array'  => 'El inventario debe estar en formato de arreglo.',
            'Inventory.min'    => 'Debe incluir al menos un registro de inventario.',

            'Inventory.*.WhsCode.required_with' => 'El código del almacén es obligatorio cuando se especifica inventario.',
            'Inventory.*.WhsCode.string'        => 'El código del almacén debe ser una cadena de texto.',
            'Inventory.*.WhsCode.max'           => 'El código del almacén no debe exceder los 8 caracteres.',

            'Inventory.*.MinStock.required_with' => 'El stock mínimo es obligatorio cuando se especifica inventario.',
            'Inventory.*.MinStock.numeric'       => 'El stock mínimo debe ser un número.',
            'Inventory.*.MinStock.min'           => 'El stock mínimo no puede ser negativo.',

            'Inventory.*.MaxStock.required_with' => 'El stock máximo es obligatorio cuando se especifica inventario.',
            'Inventory.*.MaxStock.numeric'       => 'El stock máximo debe ser un número.',
            'Inventory.*.MaxStock.gt'            => 'El stock máximo debe ser mayor que el stock mínimo.',
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
