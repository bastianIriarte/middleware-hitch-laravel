<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | SAP Service Layer Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la conexión con SAP Business One Service Layer
    |
    */

    'service_layer' => [
        'base_url' => env('SAP_SERVICE_LAYER_URL', 'https://localhost:50000'),
        'timeout' => env('SAP_SERVICE_LAYER_TIMEOUT', 30),
        'verify_ssl' => env('SAP_SERVICE_LAYER_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | SAP Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración de la base de datos de SAP
    |
    */
    
    'database' => env('SAP_DATABASE', 'SBODemoUS'),
    'username' => env('SAP_USERNAME', 'manager'),
    'password' => env('SAP_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | SAP Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configuraciones específicas para la integración
    |
    */

    'integration' => [
        'default_warehouse' => env('SAP_DEFAULT_WAREHOUSE', '01'),
        'default_item_group' => env('SAP_DEFAULT_ITEM_GROUP', 100),
        'default_uom_group' => env('SAP_DEFAULT_UOM_GROUP', 1),
        'default_currency' => env('SAP_DEFAULT_CURRENCY', 'USD'),
        'max_retries' => env('SAP_MAX_RETRIES', 3),
        'retry_delay' => env('SAP_RETRY_DELAY', 1000), // milisegundos
    ],

    /*
    |--------------------------------------------------------------------------
    | SAP Error Codes
    |--------------------------------------------------------------------------
    |
    | Códigos de error comunes de SAP para manejo específico
    |
    */

    'error_codes' => [
        'duplicate_key' => [-5002, -2035],
        'not_found' => [-2028],
        'invalid_value' => [-5001],
        'permission_denied' => [-102],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración de logs para SAP
    |
    */

    'logging' => [
        'enabled' => env('SAP_LOGGING_ENABLED', true),
        'log_requests' => env('SAP_LOG_REQUESTS', true),
        'log_responses' => env('SAP_LOG_RESPONSES', true),
        'log_errors' => env('SAP_LOG_ERRORS', true),
        'channel' => env('SAP_LOG_CHANNEL', 'daily'),
    ],

];