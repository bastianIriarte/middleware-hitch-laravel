<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRut implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Limpiar el RUT, eliminando cualquier caracter no numérico o la letra K
        $r = strtoupper(preg_replace('/[^Kk0-9]/i', '', $value));

        // Validar si el RUT es una secuencia repetida o si tiene menos de 7 caracteres
        if ($r == '111111111' || $r == '222222222' || $r == '333333333' || 
            $r == '444444444' || $r == '555555555' || $r == '666666666' || 
            $r == '777777777' || $r == '888888888' || $r == '999999999' ||
            strlen($r) < 7) {
            return false;
        }

        // Separar el cuerpo del RUT y el dígito verificador
        $sub_rut = substr($r, 0, strlen($r) - 1);
        $sub_dv = substr($r, -1);

        // Calcular el dígito verificador
        $x = 2;
        $s = 0;
        for ($i = strlen($sub_rut) - 1; $i >= 0; $i--) {
            if ($x > 7) {
                $x = 2;
            }
            $s += $sub_rut[$i] * $x;
            $x++;
        }

        // Obtener el dígito verificador esperado
        $dv = 11 - ($s % 11);
        if ($dv == 10) {
            $dv = 'K';
        } elseif ($dv == 11) {
            $dv = '0';
        }

        // Comparar el dígito verificador calculado con el ingresado
        return $dv == $sub_dv;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El RUT no es válido.';
    }
}
