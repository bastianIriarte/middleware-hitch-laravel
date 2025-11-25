<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ErrorReportRequest extends FormRequest
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
    /**
     * Prepare the data for validation.
     * Convierte camelCase a snake_case para compatibilidad
     */
    protected function prepareForValidation()
    {
        // Convertir camelCase a snake_case si vienen en camelCase
        $data = [];

        if ($this->has('companyCode')) {
            $data['company_code'] = $this->input('companyCode');
        }

        if ($this->has('fileTypeCode')) {
            $data['file_type_code'] = $this->input('fileTypeCode');
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function rules()
    {
        return [
            'company_code' => 'required|string',
            'file_type_code' => 'required|string',
            'errors' => 'required|array',
            // Formato flexible: acepta tanto el formato nuevo (line, error) como el antiguo (error_type, error_message)
            'errors.*.line' => 'nullable|string',
            'errors.*.error' => 'nullable', // Puede ser string o array
            'errors.*.error_type' => 'nullable|string',
            'errors.*.error_message' => 'nullable|string',
            'errors.*.error_details' => 'nullable|string',
            'errors.*.line_number' => 'nullable|integer',
            'errors.*.record_data' => 'nullable|string',
            'errors.*.severity' => 'nullable|in:low,medium,high,critical',
        ];
    }

    public function messages()
    {
        return [
            'company_code.required' => 'El código de empresa es requerido',
            'company_code.exists' => 'La empresa no existe',
            'file_type_code.required' => 'El tipo de archivo es requerido',
            'file_type_code.exists' => 'El tipo de archivo no existe',
            'errors.required' => 'Los errores son requeridos',
            'errors.array' => 'Los errores deben ser un arreglo',
            'errors.*.error_type.required' => 'El tipo de error es requerido',
            'errors.*.error_message.required' => 'El mensaje de error es requerido',
        ];
    }
}
