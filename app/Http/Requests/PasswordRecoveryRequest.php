<?php

namespace App\Http\Requests;

use App\Rules\ValidRut;
use Illuminate\Foundation\Http\FormRequest;

class PasswordRecoveryRequest extends FormRequest
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
            'recuperation_method' => ['required', 'in:email,rut'],
            'email' => ['email', 'nullable'],
            'rut' => [new ValidRut, 'nullable']
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('email', 'required', function ($input) {
            return $input->recuperation_method == 'email';
        });

        $validator->sometimes('rut', 'required', function ($input) {
            return $input->recuperation_method == 'rut';
        });
    }

    public function messages()
    {
        return [
            'email.required' => 'Correo electrónico Requerido',
            'email.email' => 'Correo electrónico debe ser un correo válido',
            'rut.required' => 'Rut Requerido',
        ];
    }
}
