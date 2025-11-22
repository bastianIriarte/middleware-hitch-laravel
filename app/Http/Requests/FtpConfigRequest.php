<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FtpConfigRequest extends FormRequest
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
            'company_id' => 'required|exists:companies,id',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'root_path' => 'nullable|string|max:255',
            'ssl' => 'nullable|boolean',
            'passive' => 'nullable|boolean',
            'timeout' => 'nullable|integer|min:1|max:300',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'company_id.required' => 'La empresa es requerida',
            'company_id.exists' => 'La empresa no existe',
            'host.required' => 'El host es requerido',
            'port.required' => 'El puerto es requerido',
            'port.integer' => 'El puerto debe ser un número',
            'port.min' => 'El puerto debe ser mayor a 0',
            'port.max' => 'El puerto debe ser menor a 65535',
            'username.required' => 'El usuario es requerido',
            'password.required' => 'La contraseña es requerida',
        ];
    }
}
