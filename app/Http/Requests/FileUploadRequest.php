<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
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
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'records_count' => 'nullable|integer|min:0',
            'rejected_count' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'El archivo es requerido',
            'file.file' => 'Debe ser un archivo válido',
            'file.mimes' => 'El archivo debe ser de tipo: csv, txt, xlsx, xls',
            'file.max' => 'El archivo no debe ser mayor a 10MB',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'Error de validación',
                'total' => 0,
                'data' => [
                    'errors' => $validator->errors()->all()
                ]
            ], 422)
        );
    }
}
