<?php

function pre_die($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
    die();
}

function pre($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

function authUserId(){
	return auth()->user()->id;
}

function isSuperUser(){
	if(auth()->user()->profile_id == 1){
		return true;
	}else{
		false;
	}
}

function notSuperUser(){
	if(auth()->user()->profile_id != 1){
		return true;
	}else{
		false;
	}
}

function getProfile(){
	return auth()->user()->profile_id;
}

function validateEmail($email)
{
	if ((strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	} else {
		return false;
	}
}

//Validate text/String 
function validateText($text)
{
	if ((strlen($text) < 3) || !is_string($text)) {
		return true;
	} else {
		return false;
	}
}

function validatePassword($text)
{
	if ((strlen($text) < 4)) {
		return true;
	} else {
		return false;
	}
}

//Validate Date
function validateDate($date, $format = 'Y-m-d')
{
	if (validateDateFormat($date, $format)) {
		return false;
	} else {
		return true;
	}
}

//Validate Date format
function validateDateFormat($date, $format = 'Y-m-d')
{
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}

function validateRut($rut)
{
	$r = strtoupper(preg_replace('/[^Kk0-9]/i', '', $rut));
	if ($r == '111111111' || $r == '222222222' || $r == '333333333' || $r == '444444444' || $r == '555555555' || $r == '666666666' || $r == '777777777' || $r == '888888888' || $r == '999999999') {
		return false;
	}
	if (strlen($r) < 7) {
		return false;
	}
	$sub_rut = substr($r, 0, strlen($r) - 1);
	$sub_dv = substr($r, -1);
	$x = 2;
	$s = 0;
	for ($i = strlen($sub_rut) - 1; $i >= 0; $i--) {
		if ($x > 7) {
			$x = 2;
		}
		$s += $sub_rut[$i] * $x;
		$x++;
	}
	$dv = 11 - ($s % 11);
	if ($dv == 10) {
		$dv = 'K';
	}
	if ($dv == 11) {
		$dv = '0';
	}
	if ($dv == $sub_dv) {
		return true;
	} else {
		return false;
	}
}


function str_limit($value, $limit = '', $end = ''){
	if(empty($limit)){
		$limit = 100;
	}
	if (mb_strwidth($value, 'UTF-8') <= $limit) {
			return $value;
	}
	return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
}


function formateaRut($rut)
{
	$rutLimpio = str_replace('.', '', $rut);
	$rutLimpio = trim(str_replace('-', '', $rutLimpio));
	$dvRut = substr($rutLimpio, -1);
	$rutLimpio = substr($rutLimpio, 0, -1);
	if(is_numeric($rutLimpio)){
		$rutFormateado = format_number($rutLimpio) . '-' . $dvRut;
	}else{
		$rutFormateado = ($rutLimpio) . '-' . $dvRut;
	}
	return $rutFormateado;
}

function format_money($numero)
{
	if(!empty($numero))
	{
		$pesos = '$'.number_format($numero, 0, ',', '.');
	}
	else
	{
		$pesos = "No aplica";
	}
	return $pesos;
}

function format_number($numero)
{
	if(!empty($numero))
	{
		$pesos = ''.number_format($numero, 0, ',', '.');
	}
	else
	{
		$pesos = "No aplica";
	}
	return $pesos;
}

function format_percentage($numero)
{
	
	if(!empty($numero))
	{
		if(!is_float($numero))
		{
			 
		}
		$porcentaje = str_replace('.', ',', $numero).' %'; 
	}
	else
	{
		$porcentaje = "No aplica";
	}
	return $porcentaje;
}

function limpiarStr($str){
	$str = trim($str);
	$str = str_replace('á', 'a', $str);
	$str = str_replace('é', 'e', $str);
	$str = str_replace('í', 'i', $str);
	$str = str_replace('ó', 'o', $str);
	$str = str_replace('ú', 'u', $str);
	$str = str_replace('ñ', 'n', $str);
	return $str;
}
function limpiaMoneda($str){
	$str = trim($str);
	$str = str_replace('$', '', $str);
	$str = str_replace('.', '', $str);
	$str = preg_replace('([^0-9])', '', $str);
	return $str;
}

function limpiaMonedaDecimal($str) {
    $str = trim($str);
	$str = str_replace('$', '', $str);
	$str = str_replace('.', '', $str);
	$str = str_replace(',', '.', $str);
	$str = preg_replace('/[^0-9\.]/', '', $str);
    
    return (float) $str;
}


function strUpper($str)
{
	$str = strtoupper(trim($str));
	$str = str_replace('á', 'Á', $str);
	$str = str_replace('é', 'É', $str);
	$str = str_replace('í', 'Í', $str);
	$str = str_replace('ó', 'Ó', $str);
	$str = str_replace('ú', 'Ú', $str);
	$str = str_replace('ñ', 'Ñ', $str);
	return $str;
}

function strUpperSinTildes($str)
{
	$str = strtoupper(trim($str));
	$str = str_replace('á', 'A', $str);
	$str = str_replace('Á', 'A', $str);
	$str = str_replace('é', 'E', $str);
	$str = str_replace('É', 'E', $str);
	$str = str_replace('í', 'I', $str);
	$str = str_replace('Í', 'I', $str);
	$str = str_replace('ó', 'O', $str);
	$str = str_replace('Ó', 'O', $str);
	$str = str_replace('ú', 'U', $str);
	$str = str_replace('Ú', 'U', $str);
	$str = str_replace('ñ', 'Ñ', $str);
	return $str;
}

function strLower($str)
{
	$str = strtolower(trim($str));
	$str = str_replace('Á', 'á', $str);
	$str = str_replace('É', 'é', $str);
	$str = str_replace('Í', 'í', $str);
	$str = str_replace('Ó', 'ó', $str);
	$str = str_replace('Ú', 'ú', $str);
	$str = str_replace('Ñ', 'ñ', $str);
	return $str;
}

function strCapital($str)
{
	$str = strtolower(trim($str));
	$str = str_replace('Á', 'á', $str);
	$str = str_replace('É', 'é', $str);
	$str = str_replace('Í', 'í', $str);
	$str = str_replace('Ó', 'ó', $str);
	$str = str_replace('Ú', 'ú', $str);
	$str = str_replace('Ñ', 'ñ', $str);
	return ucwords($str);
}


function sendResponse($status, $message, $data = array())
{
	header('Content-Type: application/json');

	$response = [
		'result' => $status,  
		'data' => $data
	];
	if(!empty($message))
	{
		$response['message'] = $message;
	}

	echo json_encode($response);
	exit;
}

function getToken()
{
	return sha1(strtotime(ahoraServidor()));
}

function msg_success_create($additional_text = '')
{
	return "Se ha creado $additional_text correctamente.";
}
function msg_error_create($additional_text = '')
{
	return "Ha Ocurrido un problema al crear $additional_text. Intentelo Nuevamente, si problema persiste contácte a Soporte";
}

function msg_success_update($additional_text = '')
{
	return "Se ha modificado $additional_text correctamente.";
}
function msg_error_update($additional_text = '')
{
	return "Ha Ocurrido un problema al modificar $additional_text. Intentelo Nuevamente, si problema persiste contácte a Soporte";
}

function icon_extension($name)
{
	$ext_file = substr($name, strpos($name, '.'));

	$icon = null;
	switch ($ext_file) {
		case '.txt':
			$icon = 'fa fa-file-o';
			break;

		case '.pdf':
			$icon = 'fa fa-file-pdf-o';
			break;

		case '.png' || '.PNG' || '.jpg' || '.JPG' || '.webp':
			$icon = 'fa fa-file-image-o';
			break;

		default:
			$icon = 'fa fa-file-o';
			break;
	}
	return $icon;
}

function generateSecurePassword($length = 15)
{
    // Definir los caracteres permitidos
    $upperCase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowerCase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $specialCharacters = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    // Combinar todos los caracteres en un solo string
    $allCharacters = $upperCase . $lowerCase . $numbers . $specialCharacters;

    // Asegurarse de que la contraseña contenga al menos un carácter de cada tipo
    $password = '';
    $password .= $upperCase[random_int(0, strlen($upperCase) - 1)];
    $password .= $lowerCase[random_int(0, strlen($lowerCase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $specialCharacters[random_int(0, strlen($specialCharacters) - 1)];

    // Llenar el resto de la contraseña con caracteres aleatorios
    for ($i = 4; $i < $length; $i++) {
        $password .= $allCharacters[random_int(0, strlen($allCharacters) - 1)];
    }

    // Mezclar la contraseña para evitar un patrón predecible
    return str_shuffle($password);
}

function formatSize($bytes)
{
	if ($bytes < 1024) {
		// If the file is less than 1 KB, return in bits (b)
		$bits = $bytes * 8; // Convert bytes to bits
		return "{$bits} B";
	}

	$kb = 1024; // 1 KB = 1024 bytes
	$mb = 1024 * 1024; // 1 MB = 1024 KB
	$gb = 1024 * 1024 * 1024; // 1 GB = 1024 MB
	$tb = 1024 * 1024 * 1024 * 1024; // 1 TB = 1024 GB

	if ($bytes < $mb) {
		// If the file size is greater than 1 KB but less than 1 MB
		$kbSize = $bytes / $kb;
		return round($kbSize, 2) . ' KB';
	}

	if ($bytes < $gb) {
		// If the file size is greater than 1 MB but less than 1 GB
		$mbSize = $bytes / $mb;
		return round($mbSize, 2) . ' MB';
	}

	if ($bytes < $tb) {
		// If the file size is greater than 1 GB but less than 1 TB
		$gbSize = $bytes / $gb;
		return round($gbSize, 2) . ' GB';
	}

	// If the file size is greater than or equal to 1 TB
	$tbSize = $bytes / $tb;
	return round($tbSize, 2) . ' TB';
}

function integrationLog($software, $resource_name, $create_body='', $request_body='', $code, $message, $response='', $status)
{
	$data = [
		'resource_name' => $resource_name,
		'create_body' => $create_body,
		'request_body' => $request_body,
		'code' => $code,
		'message' => $message,
		'response' => $response,
		'status' => $status
	];

	// pre([$data, $software]);

	try {
		switch ($software) {
			case 'BUK':
				$insert = App\Models\BukIntegrationResults::create($data);
				if ($insert) {
					return $insert->id;
				} else {
					return false;
				}
				break;
	
			case 'INVENTORY':
				$insert = App\Models\InventoryIntegrationResults::create($data);
				if ($insert) {
					return $insert->id;
				} else {
					return false;
				}
				break;
	
			case 'ODOO':
				$insert = App\Models\OdooIntegrationResults::create($data);
				if ($insert) {
					return $insert->id;
				} else {
					return false;
				}
				break;

			case 'FORMULARIOS-BF':
				$insert = App\Models\FormulariosBfIntegrationResults::create($data);
				if ($insert) {
					return $insert->id;
				} else {
					return false;
				}
				break;
			
			default:
				return false;
				break;
		}
	} catch (Exception $e) {
		return $e->getMessage();
	}
}