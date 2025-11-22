<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Helpers\IntegrationLogger;
use App\Http\Requests\Sap\ArticleStoreRequest;
use App\Http\Requests\Sap\ArticleUpdateRequest;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArticlesService
{
    protected $sapService;

    public function __construct()
    {
        $this->sapService = new SapServiceLayerService();
    }

    public function storeArticle(array $data, $integrationLog_id)
    {
        // 1. Actualizar log al comenzar integración
        IntegrationLogger::update('articles', $integrationLog_id, [
            'create_body' => json_encode($data),
            'attempts' => 1,
            'status_integration_id' => 2,
        ]);

        // 2. Procesar creación
        $response = $this->createBatch($data);
        // if (is_array($data) && array_is_list($data)) {
        //     $response = $this->createBatch($data);
        // } else {
        //     $response = $this->createSingle($data);
        // }

        // 3. Determinar estado final y actualizar log
        $statusId = $response['success'] ? 3 : 4; // 3=Éxito, 4=Error

        IntegrationLogger::update('articles', $integrationLog_id, [
            'code' => $response['code'],
            'status_integration_id' => $statusId,
            'message' => $response['message'] ?? 'Proceso finalizado',
            'response' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);

        return $response;
    }

    /**
     * Creación artículos 1a1
     */
    private function createSingle($data)
    {
        // 1. Validar data
        $validated = $this->validateData($data);
        if (!$validated['success']) {
            return [
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validated['errors']
            ];
        }

        // 2. Formatear data
        $formattedData = $this->prepareItemData($data);

        // 3. Enviar a SAP
        $sapResponse = $this->sendToSap($formattedData);

        return $sapResponse;
    }

    /**
     * Creación artículos batch
     */
    private function createBatch2($data)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        // --- 1. Obtener códigos del lote que ya existen en SAP, en chunks ---
        $itemCodes = array_map(fn($i) => strtoupper(trim($i['ItemCode'] ?? '')), $data);
        $itemCodes = array_filter($itemCodes); // eliminar vacíos
        $itemCodes = array_values(array_unique($itemCodes)); // códigos únicos

        $existingItemCodes = [];

        if (count($itemCodes) < 1) {
            return [
                'code'    => 400,
                'success' => 0,
                'message' => 'No se encontraron artículos a enviar',
                'data'    => []
            ];
        }
        $codes = "";
        foreach ($itemCodes as  $itemCode) {
            $codes .= "'$itemCode',";
        }
        if ($codes != "") {
            $codes = rtrim($codes, ',');
        }

        $queryApiService = app(\App\Services\QueryApiService::class);
        $searchProducts = $queryApiService->executeQuery("SELECT \"ItemCode\" FROM \"OITM\" WHERE \"ItemCode\" in ($codes)");
        $existingItemCodes = array_map('strtoupper', array_column($searchProducts['body']['data'], 'ItemCode'));

        // --- 2. Procesar los artículos ---
        $results = [];
        $totalItems = count($data);
        $success = 0;
        $error = 0;
        $duplicated = 0;
        $uniqueCodes = [];

        foreach ($data as $item) {
            Log::info($item);
            $itemCode = strtoupper(trim($item['ItemCode'] ?? ''));

            // 2. Validar ItemCode
            $validator = Validator::make($item, [
                'ItemCode' => 'required|string|max:50'
            ], [
                'ItemCode.required' => 'El código del artículo es obligatorio.',
                'ItemCode.string'   => 'El código del artículo debe ser una cadena de texto.',
                'ItemCode.max'      => 'El código del artículo no debe exceder los 50 caracteres.',
            ]);

            if ($validator->fails()) {
                $results[] = [
                    'ItemCode' => $itemCode,
                    'success' => false,
                    'message' => 'Error al procesar artículo.',
                    'errors' => ApiResponse::flattenToString($validator->errors()->all()),
                ];
                $error++;
                continue;
            }

            // Verificar duplicados en el mismo batch
            if (in_array($itemCode, $uniqueCodes)) {
                $duplicated++;
                $results[] = [
                    'ItemCode' => $itemCode,
                    'success' => false,
                    'message' => 'Código duplicado dentro del batch.',
                    'errors' => ['El código ya fue incluido previamente en esta misma carga.']
                ];
                continue;
            }

            $uniqueCodes[] = $itemCode;

            // 4. Validar si código existe
            if (in_array($itemCode, $existingItemCodes)) {
                // Actualizar en SAP
                $validated = $this->validateData($item, 'UPDATE');
                if (!$validated['success']) {
                    $results[] = [
                        'ItemCode' => $itemCode,
                        'success' => false,
                        'message' => 'Error al actualizar artículo.',
                        'errors' => $validated['errors']
                    ];
                    $error++;
                    continue;
                }
                $formattedData = $this->prepareItemDataForUpdate($item);
                $response = $this->updateToSap($formattedData, $item['ItemCode']);
            } else {
                // Crear en SAP
                $validated = $this->validateData($item, 'STORE');
                if (!$validated['success']) {
                    $results[] = [
                        'ItemCode' => $itemCode,
                        'success' => false,
                        'message' => 'Error al insertar artículo.',
                        'errors' => $validated['errors']
                    ];
                    $error++;
                    continue;
                }
                $formattedData = $this->prepareItemData($item);
                $response = $this->sendToSap($formattedData);
            }

            // Resultado
            if ($response['success']) {
                $success++;
            } else {
                $error++;
            }

            $results[] = [
                'ItemCode' => $itemCode,
                'success' => $response['success'] ?? '',
                'message' => $response['message'] ?? '',
                'errors' => $validated['errors'] ?? $response['response']['errors'] ?? []
            ];
        }

        return [
            'code'    => $success > 0 ? 200 : 400,
            'success' => $success > 0 ? 1 : 0,
            'message' => ($success > 0 ? 'Artículos enviados correctamente' : 'Error al enviar artículos.') .
                " Artículos recibidos: $totalItems, Artículos duplicados: $duplicated, Envíos exitosos: $success, Envíos erróneos: $error",
            'data'    => $results
        ];
    }


    private function createBatch($data)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $sapService = app(\App\Services\SapServiceLayerService::class);
        $queryApiService = app(\App\Services\QueryApiService::class);

        // --- 1. Obtener códigos existentes en SAP ---
        $itemCodes = array_map(fn($i) => strtoupper(trim($i['ItemCode'] ?? '')), $data);
        $itemCodes = array_filter($itemCodes);
        $itemCodes = array_values(array_unique($itemCodes));

        if (count($itemCodes) < 1) {
            return [
                'code'    => 400,
                'success' => 0,
                'message' => 'No se encontraron artículos a enviar',
                'data'    => []
            ];
        }

        $codes = implode(',', array_map(fn($c) => "'$c'", $itemCodes));
        $searchProducts = $queryApiService->executeQuery("SELECT \"ItemCode\" FROM \"OITM\" WHERE \"ItemCode\" in ($codes)");
        $existingItemCodes = array_map('strtoupper', array_column($searchProducts['body']['data'] ?? [], 'ItemCode'));

        // --- 2. Validaciones previas y preparación de jobs para el pool ---
        $jobs = [];
        $results  = [];
        $uniqueCodes = [];

        foreach ($data as $i => $item) {
            $itemCode = strtoupper(trim($item['ItemCode'] ?? ''));

            $validator = Validator::make($item, [
                'ItemCode' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                $results[$itemCode] = [
                    'ItemCode' => $itemCode,
                    'success'  => false,
                    'message'  => 'Error al procesar artículo.',
                    'errors'   => ApiResponse::flattenToString($validator->errors()->all()),
                ];
                continue;
            }

            if (in_array($itemCode, $uniqueCodes)) {
                $results[$itemCode] = [
                    'ItemCode' => $itemCode,
                    'success'  => false,
                    'message'  => 'Código duplicado dentro del batch.',
                    'errors'   => ['El código ya fue incluido previamente en esta misma carga.']
                ];
                continue;
            }

            $uniqueCodes[] = $itemCode;

            // Preparar payload y job
            if (in_array($itemCode, $existingItemCodes)) {
                $validated = $this->validateData($item, 'UPDATE');
                if (!$validated['success']) {
                    $results[$itemCode] = [
                        'ItemCode' => $itemCode,
                        'success'  => false,
                        'message'  => 'Error al actualizar artículo.',
                        'errors'   => $validated['errors']
                    ];
                    continue;
                }
                $payload = $this->prepareItemDataForUpdate($item);
                $method  = 'PATCH';
                // usar el ItemCode original dentro del URI (no forzar mayúsculas)
                $uri     = "/Items('".rawurlencode($item['ItemCode'])."')";
            } else {
                $validated = $this->validateData($item, 'STORE');
                if (!$validated['success']) {
                    $results[$itemCode] = [
                        'ItemCode' => $itemCode,
                        'success'  => false,
                        'message'  => 'Error al insertar artículo.',
                        'errors'   => $validated['errors']
                    ];
                    continue;
                }
                $payload = $this->prepareItemData($item);
                $method  = 'POST';
                $uri     = "/Items";
            }

            // Agregamos job para el pool
            $jobs[] = [
                'itemCode' => $itemCode,
                'method'   => $method,
                'uri'      => $uri,
                'payload'  => $payload,
                'headers'  => [ 'Prefer' => 'return=representation' ]
            ];
        }

        // --- 3. Ejecutar pool mediante el servicio ---
        if (count($jobs) > 0) {
            try {
                $poolResults = $sapService->executePool($jobs, 5); // concurrency = 5
            } catch (\Throwable $e) {
                Log::error('Error ejecutando executePool: ' . $e->getMessage());
                // marcar todos los jobs como error si la ejecución falló por completo
                foreach ($jobs as $j) {
                    $ic = $j['itemCode'] ?? null;
                    $results[$ic] = [
                        'ItemCode' => $ic,
                        'success'  => false,
                        'message'  => 'Error ejecutando pool: ' . $e->getMessage(),
                        'errors'   => [$e->getMessage()]
                    ];
                }
                $poolResults = [];
            }

            // Mapear resultados del pool
            foreach ($poolResults as $itemCode => $res) {
                $results[$itemCode] = [
                    'ItemCode' => $itemCode,
                    'success'  => (bool)($res['success'] ?? false),
                    'message'  => $res['message'] ?? '',
                    'errors'   => $res['errors'] ?? []
                ];
            }
        }

        // --- 4. Resumen ---
        $success = collect($results)->where('success', true)->count();
        $error   = collect($results)->where('success', false)->count();

        return [
            'code'    => $success > 0 ? 200 : 400,
            'success' => $success > 0 ? 1 : 0,
            'message' => "Artículos recibidos: " . count($data) .
                ", Envíos exitosos: $success, Envíos erróneos: $error",
            'data'    => array_values($results)
        ];
    }


    /**
     * Validar datos para crear/actualizar artículo
     */
    private function validateData(array $data, $transaction = 'STORE'): array
    {
        $request = null;

        if ($transaction == 'UPDATE') {
            $request = new ArticleUpdateRequest();
        } else {
            $request = new ArticleStoreRequest();
        }

        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }

        return ['success' => true];
    }

    /**
     * Construye el cuerpo del $batch con formato multipart/mixed
     */
    private function generateBatchPayload(array $items): array
    {
        $batchBoundary = 'batch_' . Str::uuid();
        $changeSetBoundary = 'changeset_' . Str::uuid();

        $lines = [];
        $lines[] = "--$batchBoundary";
        $lines[] = "Content-Type: multipart/mixed; boundary=$changeSetBoundary";
        $lines[] = "";

        foreach ($items as $i => $item) {
            $lines[] = "--$changeSetBoundary";
            $lines[] = "Content-Type: application/http";
            $lines[] = "Content-Transfer-Encoding: binary";
            $lines[] = "Content-ID: " . ($i + 1);
            $lines[] = "";
            $lines[] = "POST Items HTTP/1.1";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = json_encode($item, JSON_UNESCAPED_UNICODE);
        }

        $lines[] = "--$changeSetBoundary--";
        $lines[] = "--$batchBoundary--";

        return [
            'body' => implode("\r\n", $lines),
            'boundary' => $batchBoundary
        ];
    }

    /**
     * Enviar datos formateados a SAP
     */
    private function sendToSap(array $data): array
    {
        try {
            $response = $this->sapService->post('/Items', $data);

            return [
                'success' => true,
                'message' => 'Artículo creado exitosamente',
                'data' => [
                    'ItemCode' => $data['ItemCode']
                ]
            ];
        } catch (\Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());

            Log::error('Error al crear artículo: ' . $formattedException->message, [
                'request_data' => $data,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            return [
                'success' => false,
                'message' => 'Error al crear artículo.',
                'errors' => ApiResponse::flattenToString([
                    'user_message' => $errorDetails['user_message'],
                    'error_code' => $errorDetails['error_code'],
                    'suggestions' => $errorDetails['suggestions'],
                    'field' => $errorDetails['technical_details']['field'] ?? null,
                    'value' => $errorDetails['technical_details']['value'] ?? null,
                    'original_error' => config('app.debug') ? $formattedException->message : null
                ])
            ];
        }
    }

    /**
     * Enviar datos formateados a SAP (update)
     */
    private function updateToSap(array $data, $itemCode): array
    {
        try {
            unset($data['ItemCode']);

            $response = $this->sapService->patch("/Items('{$itemCode}')", $data);

            return [
                'success' => true,
                'message' => 'Artículo actualizado exitosamente',
                'data' => [
                    'ItemCode' => $itemCode,
                    'updated_fields' => array_keys($data)
                ]
            ];
        } catch (Exception $e) {
            $formattedException = SapServiceLayerService::SapFormattedException($e->getMessage());
            Log::error('Error al actualizar artículo: ' . $formattedException->message, [
                'request_data' => $data,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $formattedException->message,
                $e->getCode()
            );

            return [
                'success' => false,
                'message' => 'Error al actualizar artículo.',
                'errors' => ApiResponse::flattenToString([
                    'user_message' => $errorDetails['user_message'],
                    'error_code' => $errorDetails['error_code'],
                    'suggestions' => $errorDetails['suggestions'],
                    'original_error' => config('app.debug') ? $e->getMessage() : null
                ])
            ];
        }
    }

    /**
     * Enviar datos formateados Batch a SAP
     */
    private function sendBatchToSap(array $data): array
    {
        try {
            $boundary = $data['boundary']; // viene de generateBatchPayload()
            $body = $data['body'];

            $headers = [
                'Content-Type' => "multipart/mixed;boundary=$boundary",
                //'Prefer'=> 'odata.continue-on-error'
            ];

            // Utiliza directamente el cliente HTTP o el wrapper de sapService
            $response = $this->sapService->rawPost('/$batch', $body, $headers);

            return [
                'success' => true,
                'message' => 'Artículos enviados a SAP exitosamente',
                'data' => [
                    'raw_response' => $response // podrías parsear si lo necesitas
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error al crear artículos: ' . $e->getMessage(), [
                'request_data' => $data,
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            $errorDetails = \App\Services\SapErrorHandlerService::parseError(
                $e->getMessage(),
                $e->getCode()
            );

            return [
                'success' => false,
                'message' => $errorDetails['user_message'],
                'errors' => [
                    'error_code' => $errorDetails['error_code'],
                    'suggestions' => $errorDetails['suggestions'],
                    'field' => $errorDetails['technical_details']['field'] ?? null,
                    'value' => $errorDetails['technical_details']['value'] ?? null,
                    'original_error' => config('app.debug') ? $e->getMessage() : null
                ]
            ];
        }
    }


    public function mapItemType(string $type): string
    {
        $type = strtoupper($type);
        switch ($type) {
            case 'I':
                return 'itItems';
            case 'L':
                return 'itLabor';
            case 'T':
                return 'itTravel';
            case 'F':
                return 'itFixedAssets';
            default:
                return 'itItems'; // Valor por defecto
        }
    }



    /**
     * Preparar datos para crear artículo
     */
    public function prepareItemData(array $validatedData): array
    {
        $fieldMapping = [
            'ItemCode' => $validatedData['ItemCode'],
            'ItemName' => $validatedData['ItemName'],
            'ItemType' => $this->mapItemType($validatedData['ItemType']), // Siempre itItems para artículos normales
            'ItemsGroupCode' => $validatedData['ItmsGrpCode'],
            'UoMGroupEntry' => $validatedData['UgpEntry'],
            'InventoryItem' => $validatedData['InvntItem'] != 'tYES' ? 'tNO' : 'tYES',
            'SalesItem' => $validatedData['SellItem']  != 'tYES' ? 'tNO' : 'tYES',
            'PurchaseItem' => $validatedData['PrchseItem']  != 'tYES' ? 'tNO' : 'tYES',
            'ManageStockByWarehouse' => ($validatedData['ManageStockByWarehouse'] != 'tYES' ? 'tNO' : 'tYES')  ?? 'tYES', #MANEJA STOCK POR BODEGA
            'SWW' => $validatedData['SWW'],
            'PurchaseUnit' => $validatedData['BuyUnitMsr'],
            'SalesUnit' => $validatedData['SalUnitMsr'],
            'PurchaseItemsPerUnit' => $validatedData['PurPackUn'],
            // 'PurchaseQtyPerPackUnit' => $validatedData['PurPackUn'],
        ];
        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields($validatedData);

        $data = array_merge($fieldMapping, $arrUdf);

        // Agregar información de inventario por almacenes
        if (isset($validatedData['Inventory']) && is_array($validatedData['Inventory'])) {
            $data['ItemWarehouseInfoCollection'] = [];
            foreach ($validatedData['Inventory'] as $inventory) {
                $data['ItemWarehouseInfoCollection'][] = [
                    'WarehouseCode' => $inventory['WhsCode'],
                    'MinimalStock'  => (float) $inventory['MinStock'],  // Cambiado a MinimalStock
                    'MaximalStock'  => (float) $inventory['MaxStock']   // Cambiado a MaximalStock
                ];
            }
        }

        return $data;
    }

    /**
     * Preparar datos para enviar artículo a WMS
     */
    public function prepareWmsItemData(array $validatedData): array
    {
        $item = [
            'ItemCode' => $validatedData['ItemCode'],
            'ItemName' => $validatedData['ItemName'],
            'ItemType' => $validatedData['ItemType'], // A, L, etc.
            // 'ItemType' => "A", // A, L, etc.
            'ItmsGrpCode' => (int) $validatedData['ItmsGrpCode'],
            'UgpEntry' => (int) $validatedData['UgpEntry'],
            // 'UgpEntry' => 1,
            'InvntItem' => $validatedData['InvntItem'] === 'tYES',
            'SellItem' => $validatedData['SellItem'] === 'tYES',
            'PrchseItem' => $validatedData['PrchseItem'] === 'tYES',
            'SWW' => $validatedData['SWW'] ?? null,
            'BuyUnitMsr' => $validatedData['BuyUnitMsr'] ?? null,
            'SalUnitMsr' => $validatedData['SalUnitMsr'] ?? null,
        ];

        // UDFs (campos U_)
        $udfs = [
            'U_Comp' => $validatedData['U_COMPO'] ?? null,
            'U_Serie' => $validatedData['U_SERIE'] ?? null,
            'U_Origen' => $validatedData['U_Origen'] ?? 'FMMS', // TODO: ver de agregar este campo en la request
        ];

        // Eliminar UDFs nulos (opcional)
        $udfs = array_filter($udfs, fn($value) => !is_null($value));

        // Inventario por almacén
        $inventory = [];
        if (!empty($validatedData['Inventory']) && is_array($validatedData['Inventory'])) {
            foreach ($validatedData['Inventory'] as $inv) {
                $inventory[] = [
                    'WhsCode' => $inv['WhsCode'],
                    'MinStock' => isset($inv['MinStock']) ? (float) $inv['MinStock'] : 0,
                    'MaxStock' => isset($inv['MaxStock']) ? (float) $inv['MaxStock'] : 0,
                ];
            }
        }

        // Ensamblar resultado final
        $item = array_merge($item, $udfs);
        if (!empty($inventory)) {
            $item['inventory'] = $inventory;
        }

        return ['items' => [$item]];
    }

    /**
     * Preparar datos para actualizar artículo
     */
    /**
     * Preparar datos para actualizar artículo
     */
    public function prepareItemDataForUpdate(array $validatedData): array
    {
        $data = [];

        // Mapeo de campos directos
        $fieldMapping = [
            'ItemName'       => 'ItemName',
            'ItemType'       => 'ItemType',
            'ItmsGrpCode'    => 'ItemsGroupCode',
            'UgpEntry'       => 'UoMGroupEntry',
            'SWW'            => 'SWW',
            'ManageStockByWarehouse' => 'ManageStockByWarehouse',
            'BuyUnitMsr'     => 'PurchaseUnit',
            'SalUnitMsr'     => 'SalesUnit',
            'PurchaseItemsPerUnit' => 'PurPackUn'
        ];
        // Campos adicionales U_
        $arrUdf = $this->userDefinedFields();

        $fieldMapping = array_merge($fieldMapping, $arrUdf);

        foreach ($fieldMapping as $requestField => $sapField) {
            if (isset($validatedData[$requestField])) {
                $data[$sapField] = $validatedData[$requestField];
            }
        }

        if (isset($data['ItemType'])) {
            $data['ItemType'] = $this->mapItemType($validatedData['ItemType']);
        }
        return $data;
    }


    public function userDefinedFields($data = [])
    {
        return [
            'U_NEGOCIO'      => empty($data) ? 'U_NEGOCIO' : $data['U_NEGOCIO'],
            'U_DEPARTAMENTO' => empty($data) ? 'U_DEPARTAMENTO' : $data['U_DEPARTAMENTO'],
            'U_LINEA'        => empty($data) ? 'U_LINEA' : $data['U_LINEA'],
            'U_CLASE'        => empty($data) ? 'U_CLASE' : $data['U_CLASE'],
            'U_SERIE'        => empty($data) ? 'U_SERIE' : $data['U_SERIE'],
            'U_CONTINUIDAD'  => empty($data) ? 'U_CONTINUIDAD' : $data['U_CONTINUIDAD'],
            'U_TEMPORADA'    => empty($data) ? 'U_TEMPORADA' : $data['U_TEMPORADA'],
            'U_MARCA'        => empty($data) ? 'U_MARCA' : $data['U_MARCA'],
            'U_INTEGRACION'  => empty($data) ? 'U_INTEGRACION' : $data['U_INTEGRACION'],
            'U_ANO_CREACION' => empty($data) ? 'U_ANO_CREACION' : $data['U_ANO_CREACION'],
            'U_PROCEDENCIA'  => empty($data) ? 'U_PROCEDENCIA' : $data['U_PROCEDENCIA'],
            'U_COMPO'        => empty($data) ? 'U_COMPO' : $data['U_COMPO'],
        ];
    }
}
