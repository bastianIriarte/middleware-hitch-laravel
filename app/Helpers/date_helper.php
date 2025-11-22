<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;

function ordenar_fechaHoraServidor($date = '')
{
    if (empty($date)) {
        $date = date('Y-m-d');
    }
    $date = new DateTime($date);
    $fechaFormat = $date->format('Y-m-d H:i:s');
    return $fechaFormat;
}

function ordenar_fechaSlashHoraServidor($date = '') {
    if (empty($date)) {
        return date('Y-m-d H:i:s');
    }
    
    $fecha = DateTime::createFromFormat('d/m/Y H:i:s', $date);
    if (!$fecha) {
        $fecha = new DateTime($date);
    }
    return $fecha->format('Y-m-d H:i:s');
}

function ordenar_fechaSlashServidor($date = null)
{
    if (empty($date)) {
        return date('Y-m-d');
    }
    $dateTime = DateTime::createFromFormat('d/m/Y', trim($date));
    return $dateTime->format('Y-m-d');
}

function ordenar_fechaServidor($date)
{
    $date = new DateTime($date);
    $fechaFormat = $date->format('Y-m-d');
    return $fechaFormat;
}

function ordenar_fechaHumano($date)
{
    $explode = explode(" ", $date);
    $fecha = implode('-', array_reverse(explode('-', $explode[0])));
    return $fecha;
}

function ordenarFechaHumanoSlash($date)
{
    $explode = explode(" ", $date);
    $fecha = implode('/', array_reverse(explode('-', $explode[0])));
    return $fecha;
}

function ordenar_fechaHoraHumano($date)
{
    $explode = explode(" ", $date);
    $fecha[] = implode('-', array_reverse(explode('-', $explode[0])));
    $tiempo  = explode(":", $explode[1]);
    $fecha[] = $tiempo[0] . ':' . $tiempo[1];
    return implode(' ', $fecha);
}

function ordenar_fechaHoraMinutoHumano($date)
{
    $explode = explode(" ", $date);
    $fecha[] = implode('-', array_reverse(explode('-', $explode[0])));
    $fecha[] = $explode[1];
    return implode(' ', $fecha);
}
function ahoraServidor()
{
    return date('Y-m-d H:i:s');
}

function ahoraHumano()
{
    return date('d-m-Y H:i:s');
}

function obtenerRut($data)
{
    return substr((array_pop(explode('(', $data))), 0, -1);
}

function ahoraHumanoMesAno()
{
    $mes   = date('n');
    $meses = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
    return $meses[$mes] . ' de ' . date('Y');
}

function agregar_diasFecha($fecha, $dias, $separador = '/')
{
    $explode = explode(" ", $fecha);
    $fecha = implode('-', array_reverse(explode('-', $explode[0])));
    $fecha = str_replace('-', '/', $fecha);

    list($day, $mon, $year) = explode('/', $fecha);
    return date('d' . $separador . 'm' . $separador . 'Y', mktime(0, 0, 0, $mon, $day + $dias, $year));
}

function agregar_diasFechaServidor($fecha, $dias, $separador = '/')
{
    $explode = explode(" ", $fecha);
    $fecha = implode('-', array_reverse(explode('-', $explode[0])));
    $fecha = str_replace('-', '/', $fecha);

    list($day, $mon, $year) = explode('/', $fecha);
    return date('Y' . $separador . 'm' . $separador . 'd', mktime(0, 0, 0, $mon, $day + $dias, $year));
}

function diaSemana($dia, $mes, $ano)
{
    $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
    return $dias[date("w", mktime(0, 0, 0, $mes, $dia, $ano))];
}

function traerNumeroDia($dia)
{
    $return = '';
    $dias = array(1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo');
    foreach ($dias as $key => $value) {
        if ($value == $dia) {
            $return = $key;
        }
    }
    return $return;
}

function traerTextoDia($dia)
{
    $return = '';
    $dias = array(1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo');
    foreach ($dias as $key => $value) {
        if ($key == $dia) {
            $return = $value;
        }
    }
    return $return;
}


function traerNumeroMes($mes)
{
    $return = '';
    $meses = array(
        1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio",
        7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre"
    );
    foreach ($meses as $key => $value) {
        if ($value == $mes) {
            $return = $key;
        }
    }
    return $return;
}

function getMonthText($month)
{
    $return = '';
    $months = array(
        1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio",
        7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre"
    );

    if($months[$month]){
       $return = $months[$month];
    }
    return $return;
}

function rangoFechas($fecha_inicio, $fecha_termino, $dia, $mes)
{
    list($ano_inicio, $mes_inicio, $dia_inicio)       = explode('-', $fecha_inicio);
    list($ano_termino, $mes_termino, $dia_termino)    = explode('-', $fecha_termino);

    $dias_inicio  = cal_days_in_month(CAL_GREGORIAN, $mes_inicio, $ano_inicio);
    $dias_termino = cal_days_in_month(CAL_GREGORIAN, $mes_termino, $ano_termino);
    if ($mes_inicio == $mes_termino) {
        $dia;
    } else {
    }
}

function traerMeses()
{
    $return = '';
    return array(
        1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio",
        7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre"
    );
}

function getMonthNumber()
{
    $return = '';
    return array(
        '' => 'Seleccione...', 1 => "01", 2 => "02", 3 => "03", 4 => "04", 5 => "05", 6 => "06",
        7 => "07", 8 => "08", 9 => "09", 10 => "10", 11 => "11", 12 => "12"
    );
}

function diasEntreFechas($fecha1 = '', $fecha2 = '')
{
    if (empty($fecha1)) {
        $fecha1 = date('Y-m-d');
    }
    if (empty($fecha1)) {
        $fecha2 = date('Y-m-d');
    }
    $fecha1 = new DateTime($fecha1);
    $fecha2 = new DateTime($fecha2);
    $diff = $fecha1->diff($fecha2);
   
    return $diff->days;
}

function isValidExcelDate($value): bool
{
    // Debe ser numérico y estar dentro de un rango razonable (1900-01-01 en adelante)
    // return is_numeric($value) && $value > 25569 && $value < 60000;
    return is_numeric($value) && $value > 25569;
}

function excelDateToYmd($value): ?string
{
    if (!isValidExcelDate($value)) {
        return null;
    }

    try {
        $date = Date::excelToDateTimeObject($value);
        return $date->format('Y-m-d');
    } catch (\Exception $e) {
        return null;
    }
}
