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
    public function rules()
    {
        return [
            'company_code' => 'required|string|exists:companies,code',
            'file_type_code' => 'required|string|exists:file_types,code',
            'errors' => 'required|array',
            'errors.*.error_type' => 'required|string',
            'errors.*.error_message' => 'required|string',
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
