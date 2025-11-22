<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SapErrorHandlerService
{

    private static $requestContext = [];

    /**
     * Establece el contexto del request para mejorar los mensajes de error.
     */
    public static function setRequestContext(array $context): void
    {
        self::$requestContext = $context;
    }

    /**
     * Mapeo de códigos de error SAP a mensajes amigables
     */
    private static $errorMappings = [
        // Errores de campos obligatorios
        'does not exist' => 'valor_no_existe',
        'Linked value' => 'valor_relacionado_no_existe',
        'already exists' => 'ya_existe',
        'Invalid' => 'valor_invalido',
        'required' => 'campo_requerido',
        'cannot be empty' => 'campo_vacio',
        'too long' => 'valor_muy_largo',
        'out of range' => 'fuera_de_rango',
        'Login failed' => 'autenticacion_fallida',
        'Session expired' => 'sesion_expirada',
        'Permission denied' => 'sin_permisos',
        'Connection' => 'error_conexion',
        'timeout' => 'tiempo_agotado',
        'Internal error' => 'error_interno_sap',
        'is invalid' => 'propiedad_invalida',
        'Property' => 'propiedad_invalida',
        'BusinessPartner' => 'error_socio_negocio',
        'duplicate key' => 'clave_duplicada',
        'Undefined index' => 'indice_undefined',
        'invalid column name' => 'columna_invalida',
        'No matching records found' => 'valor_no_existe',
    ];


    /**
     * Mapeo de códigos de error numéricos SAP
     */
    private static $numericErrorCodes = [
        -5002 => 'clave_duplicada',
        -2035 => 'clave_duplicada',
        -2028 => 'valor_no_existe',
        -5001 => 'valor_invalido',
        -102 => 'sin_permisos',
        -1 => 'error_general',
        -4002 => 'campo_requerido',
        -5003 => 'valor_muy_largo',
    ];

    /**
     * Mapeo de campos SAP a nombres amigables
     */
    private static $fieldMappings = [
        'ItmsGrpCod' => 'Grupo de Artículos',
        'UgpEntry' => 'Grupo de Unidades de Medida',
        'ItemCode' => 'Código de Artículo (ItemCode)',
        'ItemName' => 'Nombre de Artículo',
        'WhsCode' => 'Código de Almacén (WhsCode)',
        'CardCode' => 'Código de Socio de Negocio (CardCode)',
        'CardName' => 'Nombre de Socio de Negocio',
        'DebPayAcct' => 'Cuenta Contable de Proveedor',
        'PayTermsGroupCode' => 'Condiciones de Pago',
        'GRouoNum' => 'Condiciones de Pago',
        'ListNum' => 'Lista de Precios',
        'GroupCode' => 'Grupo de Socio de Negocio',
        'Currency' => 'Moneda',
        'LicTradNum' => 'RUT/Identificación Fiscal',
        'Phone1' => 'Teléfono',
        'E_Mail' => 'Correo Electrónico',
        'Address' => 'Dirección',
        'Street' => 'Calle',
        'City' => 'Ciudad',
        'Country' => 'País',
        'BankCode' => 'Código de Banco',
        'Account' => 'Número de Cuenta',
        'BusinessPartner' => 'Socio de Negocio',
        'OITM' => 'Artículo',
        'OCRD' => 'Socios de Negocio',
        'OWHS' => 'Almacenes',
        'OITB' => 'Grupos de Artículos',
        'ItemWarehouseInfoCollection.WarehouseCode' => 'Código de Almacén (WhsCode)',
        'AddressName' => 'Dirección (Address)',
        'BPAddress'   => 'Dirección de Socio de Negocio (BPAddress)',
    ];

    /**
     * Procesar error de SAP y convertirlo a mensaje amigable
     */
    public static function parseError(string $errorMessage, int $statusCode = 400): array
    {
        $parsedError = self::extractErrorDetails($errorMessage);

        // Log para debugging (solo en desarrollo)
        if (config('app.debug')) {
            Log::debug('SAP Error Parsing Debug', [
                'original_message' => $errorMessage,
                'parsed_details' => $parsedError,
                'request_context' => self::$requestContext
            ]);
        }
        $suggestions = self::generateSuggestions($parsedError);
        $suggestions = implode(" | O | ", $suggestions);
        Log::debug('ERROR COMPLETE', [
            'user_message' => self::generateUserFriendlyMessage($parsedError),
            'technical_details' => $parsedError,
            'suggestions' => $suggestions,
            'error_code' => self::generateErrorCode($parsedError),
            'status_code' => self::determineStatusCode($parsedError, $statusCode)
        ]);
        return [
            'user_message' => self::generateUserFriendlyMessage($parsedError),
            'technical_details' => $parsedError,
            'suggestions' => $suggestions,
            'error_code' => self::generateErrorCode($parsedError),
            'status_code' => self::determineStatusCode($parsedError, $statusCode)
        ];
    }

    /**
     * Extraer detalles del error
     */
    private static function extractErrorDetails(string $errorMessage): array
    {
        $details = [
            'original_message' => $errorMessage,
            'field' => null,
            'table' => null,
            'value' => null,
            'error_type' => 'unknown',
            'numeric_code' => null
        ];

        $cleanMessage = trim($errorMessage);

        // Extraer códigos numéricos (Internal error o ODBC)
        if (
            preg_match('/Internal error \((-?\d+)\)/', $cleanMessage, $matches) ||
            preg_match('/\((?:ODBC )?(-?\d+)\)/', $cleanMessage, $matches)
        ) {
            $details['numeric_code'] = (int)$matches[1];
            if (isset(self::$numericErrorCodes[$details['numeric_code']])) {
                $details['error_type'] = self::$numericErrorCodes[$details['numeric_code']];
            }
        }

        // Detectar errores de tipo "Enter valid code [Campo], 'Valor'"
        if (preg_match("/Enter valid code\s+\[([A-Za-z0-9_.]+)\]\s*,\s*'([^']+)'/", $cleanMessage, $matches)) {
            $details['field'] = $matches[1];
            $details['value'] = $matches[2];
            $details['error_type'] = 'valor_no_existe';

            // Corrección especial para WarehouseCode usando el contexto del request
            if ($details['field'] === 'ItemWarehouseInfoCollection.WarehouseCode' && !empty(self::$requestContext)) {
                $details['value'] = self::$requestContext['inventory'][0]['WhsCode']
                    ?? $details['value'];
            }
            return $details;
        }

        if (preg_match('/Enter valid code \[([A-Za-z0-9_.]+)\]/', $cleanMessage, $matches)) {
            $details['field'] = $matches[1];
            $details['error_type'] = 'valor_no_existe';
            return $details;
        }

        // Detectar propiedad inválida
        if (preg_match("/Property '([^']+)' of '([^']+)' is invalid/", $cleanMessage, $matches)) {
            $details['field'] = $matches[1];
            $details['table'] = $matches[2];
            $details['error_type'] = 'propiedad_invalida';
        }

        // Detectar [TABLA.Campo]
        if (preg_match('/\[([A-Z]+)\.([A-Za-z0-9_]+)\]/', $cleanMessage, $matches)) {
            $details['table'] = $details['table'] ?: $matches[1];
            $details['field'] = $details['field'] ?: $matches[2];
        }

        if (preg_match("/Value too long in property '([^']+)' of '([^']+)'/", $cleanMessage, $matches)) {
            $details['field'] = $matches[1];  // AddressName
            $details['table'] = $matches[2];  // BPAddress
            $details['error_type'] = 'valor_muy_largo';
        }

        if (preg_match('/Undefined index: ([A-Za-z0-9_]+)/', $cleanMessage, $matches)) {
            $details['field'] = $matches[1] ?? null;
            $details['error_type'] = 'indice_undefined';
            return $details;
        }

        if (preg_match('/invalid column name:\s*([A-Za-z0-9_.]+)/i', $cleanMessage, $matches)) {
            $details['field'] = $matches[1] ?? null;
            $details['table'] = $details['table'] ?? null;
            $details['error_type'] = 'columna_invalida';
            return $details;
        }

        // Error: cuenta contable no válida en el drawer Liabilities
        if (preg_match("/Define account in\s+\"?Liabilities\"?\s+drawer\s+\[([A-Z]+)\.([A-Za-z0-9_]+)\]\s*,\s*'([^']+)'/", $cleanMessage, $matches)) {
            $details['table'] = $matches[1]; // OCRD
            $details['field'] = $matches[2]; // DebPayAcct
            $details['value'] = $matches[3]; // PCN00171
            $details['error_type'] = 'valor_no_existe';
            return $details;
        }


        // Patrones de mensajes estándar
        $patterns = [
            '/Linked value (\d+) does not exist/' => function ($matches) use (&$details) {
                $details['value'] = $matches[1];
                $details['error_type'] = 'valor_relacionado_no_existe';
            },
            '/Item \'([^\']+)\' already exists/' => function ($matches) use (&$details) {
                $details['value'] = $matches[1];
                $details['error_type'] = 'ya_existe';
            },
            '/Item code \'([^\']+)\' already exists/' => function ($matches) use (&$details) {
                $details['value'] = $matches[1];
                $details['field'] = 'ItemCode';
                $details['table'] = 'OITM';
                $details['error_type'] = 'ya_existe';
            },
            '/Value \'([^\']+)\' is invalid/' => function ($matches) use (&$details) {
                $details['value'] = $matches[1];
                $details['error_type'] = 'valor_invalido';
            },
            '/([A-Za-z0-9_]+) cannot be empty/' => function ($matches) use (&$details) {
                $details['field'] = $details['field'] ?: $matches[1];
                $details['error_type'] = 'campo_vacio';
            },
            '/([A-Za-z0-9_]+) is required/' => function ($matches) use (&$details) {
                $details['field'] = $details['field'] ?: $matches[1];
                $details['error_type'] = 'campo_requerido';
            },
            '/Login failed|Authentication failed/' => function ($matches) use (&$details) {
                $details['error_type'] = 'autenticacion_fallida';
            },
            '/Session expired|Session invalid/' => function ($matches) use (&$details) {
                $details['error_type'] = 'sesion_expirada';
            },
            '/Connection|timeout|network/' => function ($matches) use (&$details) {
                $details['error_type'] = 'error_conexion';
            },
            '/BusinessPartner/' => function ($matches) use (&$details) {
                $details['table'] = $details['table'] ?: 'BusinessPartner';
                if ($details['error_type'] === 'unknown') {
                    $details['error_type'] = 'error_socio_negocio';
                }
            },
            '/Business partner code \'([^\']+)\' already assigned/' => function ($matches) use (&$details) {
                $details['field'] = 'CardCode';
                $details['value'] = $matches[1];
                $details['error_type'] = 'ya_existe';
            },
        ];

        foreach ($patterns as $pattern => $callback) {
            if (preg_match($pattern, $cleanMessage, $matches)) {
                $callback($matches);
            }
        }

        // Buscar valores entre comillas si no hay value
        if ($details['value'] === null) {
            if (preg_match("/'([^']+)'/", $cleanMessage, $matches)) {
                $details['value'] = $matches[1];
            } elseif (preg_match('/"([^"]+)"/', $cleanMessage, $matches)) {
                $details['value'] = $matches[1];
            }
        }

        // Intentar detectar error_type por palabras clave si sigue unknown
        if ($details['error_type'] === 'unknown') {
            foreach (self::$errorMappings as $pattern => $type) {
                if (stripos($cleanMessage, $pattern) !== false) {
                    $details['error_type'] = $type;
                    break;
                }
            }
        }


        return $details;
    }

    /**
     * Generar mensaje amigable para el usuario
     */
    /**
     * Generar mensaje amigable para el usuario
     */
    private static function generateUserFriendlyMessage(array $details): string
    {
        // Usamos el mapeo si existe, sino reemplazamos '.' por ' → ' para campos anidados
        $fieldKey = $details['field'];
        $field = self::$fieldMappings[$fieldKey] ?? str_replace('.', ' → ', $fieldKey);
        $table = self::$fieldMappings[$details['table']] ?? $details['table'] ?? 'registro';
        $value = $details['value'];

        switch ($details['error_type']) {
            case 'propiedad_invalida':
                if ($details['field']) {
                    return "El campo '{$field}' no es válido para {$table}. Este campo no existe o no se puede usar en esta operación.";
                }
                return "Se está intentando usar un campo que no es válido para esta operación.";

            case 'clave_duplicada':
                if ($value) {
                    return "Ya existe un {$table} con el código '{$value}'. Use un código diferente.";
                }
                return "Ya existe un registro con los mismos datos únicos. Use valores diferentes.";

            case 'error_interno_sap':
                $message = "Error interno del sistema SAP";
                if ($details['numeric_code']) {
                    $message .= " (código {$details['numeric_code']})";
                }
                return $message . ". Contacte al administrador del sistema.";

            case 'error_socio_negocio':
                return "Error en la creación/actualización del socio de negocio. Verifique que todos los campos sean válidos.";

            case 'valor_relacionado_no_existe':
            case 'valor_no_existe':
                if ($fieldKey === 'DebPayAcct' && $value) {
                    return "El valor {$field} '" . self::$requestContext[$fieldKey] . "' no es válido para la Cuenta Contable del proveedor. Debe pertenecer al drawer 'Pasivos' en SAP.";
                } elseif ($value) {
                    return "El {$field} con código '{$value}' no existe en el sistema. Verifique que el código sea correcto.";
                } else {
                    return "El valor especificado para " . (empty($field) ? 'uno de los campos'  : $field) . " no existe en el sistema. Verifique que el código sea correcto.";
                }

            case 'ya_existe':
                if ($value) {
                    return "Ya existe un {$table} con el {$field} '{$value}'. Use un código diferente.";
                } else {
                    return "Ya existe un {$table} con ese {$field}. Use un código diferente.";
                }

            case 'valor_invalido':
                if ($value) {
                    return "El valor '{$value}' no es válido para el campo {$field}.";
                } else {
                    return "El valor especificado no es válido para el campo {$field}.";
                }

            case 'campo_requerido':
            case 'campo_vacio':
                return "El campo {$field} es obligatorio y no puede estar vacío.";

            case 'valor_muy_largo':
                return "El valor del campo {$field} excede la longitud máxima permitida en SAP.";

            case 'fuera_de_rango':
                if ($value) {
                    return "El valor '{$value}' para {$field} está fuera del rango permitido.";
                } else {
                    return "El valor para {$field} está fuera del rango permitido.";
                }

            case 'autenticacion_fallida':
                return "Error de autenticación. Verifique sus credenciales.";

            case 'sesion_expirada':
                return "Su sesión ha expirado. Por favor, autentíquese nuevamente.";

            case 'sin_permisos':
                return "No tiene permisos suficientes para realizar esta operación.";

            case 'error_conexion':
                return "Error de conexión con el servidor SAP. Intente nuevamente.";

            case 'tiempo_agotado':
                return "La operación tardó demasiado tiempo. Intente nuevamente.";
            case 'indice_undefined':
                return "El campo '{$field}' no existe en SAP.";
            case 'columna_invalida':
                return "La columna '{$field}' no existe en la tabla de SAP. Revise los nombres de los campos enviados.";

            default:
                // Si no se puede categorizar el error, mostrar un mensaje genérico más útil
                if ($field && $value) {
                    return "Error en el campo {$field}: valor '{$value}' no válido. Contacte al administrador del sistema.";
                } elseif ($field) {
                    return "Error en el campo {$field}. Verifique el valor ingresado.";
                } else {
                    return "Error procesando la solicitud. Verifique los datos ingresados y contacte al administrador si persiste.";
                }
        }
    }


    /**
     * Generar sugerencias para solucionar el error
     */
    private static function generateSuggestions(array $details): array
    {
        $suggestions = [];

        switch ($details['error_type']) {
            case 'propiedad_invalida':
                $suggestions[] = "Elimine el campo '{$details['field']}' de la solicitud";
                $suggestions[] = "Consulte la documentación del Service Layer para conocer los campos válidos";
                $suggestions[] = "Verifique que está usando la versión correcta de la API";
                break;

            case 'clave_duplicada':
                $suggestions[] = "Use un código único que no exista en el sistema";
                $suggestions[] = "Verifique si desea actualizar el registro existente";
                $suggestions[] = "Consulte los códigos existentes antes de crear nuevos registros";
                break;

            case 'error_interno_sap':
                $suggestions[] = "Verifique que todos los campos obligatorios estén presentes";
                $suggestions[] = "Revise que los valores de referencia existan en el sistema";
                $suggestions[] = "Contacte al administrador del sistema si el error persiste";
                break;

            case 'error_socio_negocio':
                $suggestions[] = "Verifique que todos los campos requeridos estén completos";
                $suggestions[] = "Asegúrese de que los códigos de referencia (GroupCode, Currency) existan";
                $suggestions[] = "Revise que no esté enviando campos no válidos para BusinessPartner";
                break;

            case 'valor_relacionado_no_existe':
            case 'valor_no_existe':
                $suggestions[] = "Verifique que el código exista en el catálogo correspondiente";
                $suggestions[] = "Consulte los valores válidos para este campo";
                if ($details['field'] === 'ItmsGrpCod') {
                    $suggestions[] = "Use GET /api/grupos-articulos para ver los grupos disponibles";
                } elseif ($details['field'] === 'GroupCode') {
                    $suggestions[] = "Use GET /api/grupos-socios para ver los grupos disponibles";
                }
                break;

            case 'ya_existe':
                $suggestions[] = "Use un código único que no exista en el sistema";
                $suggestions[] = "Verifique si desea actualizar el registro existente en lugar de crear uno nuevo";
                break;

            case 'valor_invalido':
                $suggestions[] = "Revise el formato del valor ingresado";
                $suggestions[] = "Consulte la documentación para conocer los valores permitidos";
                break;

            case 'campo_requerido':
                $suggestions[] = "Complete todos los campos marcados como obligatorios";
                $suggestions[] = "Revise la documentación de la API para conocer los campos requeridos";
                break;

            case 'indice_undefined':
                $suggestions[] = "Asegúrese de que el campo '{$details['field']}' exista en los datos enviados";
                $suggestions[] = "Revise el request para incluir todos los campos obligatorios";
                break;
            case 'columna_invalida':
                $suggestions[] = "Verifique que el nombre del campo '{$details['field']}' exista en SAP";
                $suggestions[] = "Revise la estructura de la tabla en SAP antes de realizar la solicitud";
                $suggestions[] = "Asegúrese de que el campo personalizado esté correctamente creado";
                break;

            default:
                $suggestions[] = "Verifique los datos ingresados";
                $suggestions[] = "Consulte la documentación de la API";
                $suggestions[] = "Contacte al administrador si el problema persiste";
                break;
        }

        return $suggestions;
    }

    /**
     * Generar código de error estructurado
     */
    private static function generateErrorCode(array $details): string
    {
        $prefix = 'SAP';
        $table  = $details['table'] ?? 'GEN';

        // Simplificar nombre de tabla
        switch (strtoupper($table)) {
            case 'BUSINESSPARTNER':
                $tableCode = 'BP';
                break;
            case 'ITEMS':
                $tableCode = 'ITEM';
                break;
            case 'WAREHOUSES':
                $tableCode = 'WHS';
                break;
            default:
                $tableCode = strtoupper(substr($table, 0, 3));
                break;
        }

        // Determinar tipo de error
        switch ($details['error_type']) {
            case 'propiedad_invalida':
                $type = 'INV';
                break;
            case 'clave_duplicada':
                $type = 'DUP';
                break;
            case 'error_interno_sap':
                $type = 'INT';
                break;
            case 'error_socio_negocio':
                $type = 'BP';
                break;
            case 'valor_no_existe':
                $type = 'NEX';
                break;
            case 'ya_existe':
                $type = 'EXI';
                break;
            case 'valor_invalido':
                $type = 'VAL';
                break;
            case 'campo_requerido':
                $type = 'REQ';
                break;
            default:
                $type = 'UNK';
                break;
        }

        return "{$prefix}_{$tableCode}_{$type}";
    }


    /**
     * Determinar código de estado HTTP apropiado
     */
    private static function determineStatusCode(array $details, int $default): int
    {
        switch ($details['error_type']) {
            case 'propiedad_invalida':
            case 'valor_invalido':
            case 'campo_requerido':
            case 'campo_vacio':
                return 422; // Unprocessable Entity

            case 'valor_relacionado_no_existe':
            case 'valor_no_existe':
                return 422; // Unprocessable Entity

            case 'ya_existe':
            case 'clave_duplicada':
                return 409; // Conflict

            case 'autenticacion_fallida':
                return 401; // Unauthorized

            case 'sin_permisos':
                return 403; // Forbidden

            case 'error_conexion':
            case 'tiempo_agotado':
                return 503; // Service Unavailable

            case 'error_interno_sap':
                return 500; // Internal Server Error

            default:
                return $default;
        }
    }
}
