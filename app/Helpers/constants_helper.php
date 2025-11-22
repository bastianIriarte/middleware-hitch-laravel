<?php

define('APP_NAME', "Middleware HITCH");
define('APP_VERSION', "1.0");
define('NAME_DESIGN', 'hitch.cl');
define('URL_DESIGN', 'https://hitch.cl/');
define('REMITENTE_BASE', '');
define('MAIL_BASE', '');
define('USER_MAIL_SENDGRID', '');
define('COMPANY_NAME', "Middleware HITCH");
define('TYPE_MODE', "DEV"); // PRODUCTION o DEV

$public_folder = '';
if (TYPE_MODE == 'PRODUCTION') {
    $public_folder = 'public/';
}

#REMOVER PUBLIC EN DEV
define("ASSETS",            $public_folder.'assets');
define("ASSETS_CSS",        $public_folder.'assets/css/');
define("ASSETS_LIBS",       $public_folder.'assets/libs/');
define("ASSETS_FONTS",      $public_folder.'assets/fonts/');
define("ASSETS_IMG",        $public_folder.'assets/img/');
define("ASSETS_JS",         $public_folder.'assets/js/');
define("ASSETS_PLUGINS",    $public_folder.'assets/plugins/');
define("ASSETS_VENDORS",    $public_folder.'assets/vendor/');

define("ASSETS_ADMIN",            $public_folder.'assets/admin');
define("ASSETS_CSS_ADMIN",        $public_folder.'assets/admin/css/');
define("ASSETS_LIBS_ADMIN",       $public_folder.'assets/admin/libs/');
define("ASSETS_FONTS_ADMIN",      $public_folder.'assets/admin/fonts/');
define("ASSETS_IMG_ADMIN",        $public_folder.'assets/admin/images/');
define("ASSETS_JS_ADMIN",         $public_folder.'assets/admin/js/');
define("ASSETS_PLUGINS_ADMIN",    $public_folder.'assets/admin/plugins/');
define("ASSETS_VENDORS_ADMIN",    $public_folder.'assets/admin/vendors/');

define("STORAGE",                       $public_folder.'storage/');
define("STORAGE_EXCEL_FORMATS",         $public_folder.'storage/excel/formats/');
define("STORAGE_EXCEL_UPLOAD_ERRORS",   $public_folder.'storage/excel/upload_errors/');


define('URL_LOGO', ASSETS_IMG_ADMIN.'logonaranjoftransp.png');
define('URL_LOGO_FAVICON', ASSETS_IMG_ADMIN.'ffavicon.png');
define('ALERT_MANTENIMIENTO', false);
define('PROFILES_PROTECTED', [1, 2]);