<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = [], string $message = 'Operación exitosa', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }

    public static function error(string $message = 'Ocurrió un error', array $errors = [], int $code = 400, $stringErrors=true)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'  => $stringErrors ? self::flattenToString($errors) : $errors,
            'timestamp' => now()->toIso8601String(),
        ], $code > 0 ? $code : 400);
    }

    public static function validationError(array $errors)
    {
        return self::error('Los datos proporcionados no son válidos.', $errors, 422);
    }

    public static function flattenToString($input, string $separator = ' | '): string
    {
        if (!is_array($input)) {
            return is_string($input) ? $input : (is_null($input) ? 'null' : strval($input));
        }

        $result = [];

        $flatten = function ($array, $prefix = '') use (&$flatten, &$result) {
            foreach ($array as $key => $value) {
                // Limpiar y formatear key para mayor compatibilidad
                $cleanKey = is_int($key) ? $key : trim(str_replace([' ', "\n", "\r"], '_', $key));
                $fullKey = $prefix === '' ? (string)$cleanKey : "{$prefix}.{$cleanKey}";

                if (is_array($value)) {
                    if (empty($value)) {
                        $result[] = "{$fullKey} => []";
                    } else {
                        $flatten($value, $fullKey);
                    }
                } else {
                    $formattedValue = is_null($value) ? 'null' : (is_bool($value) ? ($value ? 'true' : 'false') : strval($value));
                    $result[] = "{$fullKey} => {$formattedValue}";
                }
            }
        };

        $flatten($input);

        return implode($separator, $result);
    }
}