<?php

namespace App\Http\Requests\Sap;

use Illuminate\Foundation\Http\FormRequest;

class ArticleSendBatchesRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'Items' => 'required|array|min:1',
        ];
    }

    /**
     * Mensajes personalizados de error (opcional).
     */
    public function messages(): array
    {
        return [
            'Items.required' => 'El campo Items es obligatorio.',
            'Items.array' => 'El campo Items debe ser un array de artículos.',
            'Items.min' => 'Debe incluir al menos un artículo en el campo Items.',
        ];
    }
}
