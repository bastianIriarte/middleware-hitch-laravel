<?php

namespace App\Http\Controllers;

use App\Models\ApiConnection;
use App\Services\EncryptionService;
use Illuminate\Http\Request;

class ApiConnectionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.users');
    }

    public function index()
    {
        $sidenav = 'api_connections';
        $sidenav_item = 'api_connections_list';
        $title = 'Gestión de Conexiones';
        $title_table = 'Listado de Conexiones';

        $connections = ApiConnection::where('deleted', false)->get();

        $sap_data = $connections->where('software', 'SAP')->first();
        $wms_data = $connections->where('software', 'WMS')->first();
        $fmms_data = $connections->where('software', 'FMMS')->first();
        $query_api_data = $connections->where('software', 'QUERY_API')->first();
        $sql_pos_data = $connections->where('software', 'SQL_POS')->first();

        $integrations = [
            'SAP' => [
                'data' => $sap_data ?? null,
                'fields' => [
                    'endpoint' => ['label' => 'Url', 'placeholder' => 'https://example.com', 'type' => 'text'],
                    'database' => ['label' => 'Base de Datos', 'placeholder' => 'Ingrese Nombre de BD...', 'type' => 'text'],
                    'username' => ['label' => 'Usuario', 'placeholder' => 'Ingrese Usuario...', 'type' => 'text'],
                    'password' => ['label' => 'Contraseña', 'placeholder' => 'Ingrese contraseña...', 'type' => 'password'],
                ]
            ],
            'WMS' => [
                'data' => $wms_data ?? null,
                'fields' => [
                    'endpoint' => ['label' => 'Url', 'placeholder' => 'https://example.com', 'type' => 'text'],
                    'username' => ['label' => 'Usuario', 'placeholder' => 'Ingrese Usuario...', 'type' => 'text'],
                    'password' => ['label' => 'Contraseña', 'placeholder' => 'Ingrese ingrese contraseña...', 'type' => 'password'],
                ]
            ],
            'FMMS' => [
                'data' => $fmms_data ?? null,
                'fields' => [
                    'endpoint' => ['label' => 'Url', 'placeholder' => 'https://example.com', 'type' => 'text'],
                    'database' => ['label' => 'Base de Datos', 'placeholder' => 'Ingrese Nombre de BD...', 'type' => 'text'],
                    'username' => ['label' => 'Usuario', 'placeholder' => 'Ingrese Usuario..', 'type' => 'text'],
                    'password' => ['label' => 'Contraseña', 'placeholder' => 'ingrese contraseña..', 'type' => 'password'],
                    'api_key'  => ['label' => 'API Key', 'placeholder' => 'Ingrese API Key...', 'type' => 'password'],
                ]
            ],
            'QUERY_API' => [
                'data' => $query_api_data ?? null,
                'fields' => [
                    'endpoint' => ['label' => 'Url', 'placeholder' => 'https://example.com', 'type' => 'text'],
                    'api_key'  => ['label' => 'API Key', 'placeholder' => 'Ingrese API Key...', 'type' => 'password'],
                ]
            ],
            'SQL_POS' => [
                'data' => $sql_pos_data ?? null,
                'fields' => [
                    'endpoint' => ['label' => 'Servidor', 'placeholder' => '192.168.250.1', 'type' => 'text'],
                    'port' => ['label' => 'Puerto', 'placeholder' => 'ingrese puerto..', 'type' => 'number'],
                    'database' => ['label' => 'Base de Datos', 'placeholder' => 'Ingrese Nombre de BD...', 'type' => 'text'],
                    'username' => ['label' => 'Usuario', 'placeholder' => 'Ingrese Usuario..', 'type' => 'text'],
                    'password' => ['label' => 'Contraseña', 'placeholder' => 'ingrese contraseña..', 'type' => 'password'],
                ]
            ],
        ];

        return view('admin.api_connections.api_connections_list', compact(
            'title',
            'sidenav',
            'sidenav_item',
            'title_table',
            'integrations',
        ));
    }

    public function update(Request $request)
    {
        $integration = strUpper($request->integration);

        if (!isset($integration) || !in_array($integration, ['SAP', 'WMS', 'FMMS', 'QUERY_API', 'SQL_POS'])) {
            return back()->with([
                'danger_message' => 'Integración Inválida',
                'danger_message_title' => 'ERROR DE VALIDACIÓN'
            ]);
        }

        $encryptionService = new EncryptionService();
        $connectionData = ApiConnection::where('software', $integration)->first();
        $data = [];

        switch ($integration) {
            case 'SAP':
                $request->validate([
                    'endpoint' => 'required|string|max:255',
                    'database' => 'required|string|max:255',
                    'username' => 'required|string|max:255',
                    'password' => 'required|string|max:255',
                ]);

                $data = [
                    'endpoint' => $request->endpoint,
                    'database' => $request->database,
                    'username' => $request->username,
                ];

                $passwordDecrypt = $encryptionService->decrypt($request->password);
                if ($passwordDecrypt) {
                    $request->password = $passwordDecrypt;
                }

                $data['password'] = $encryptionService->encrypt($request->password);

                //test de conexión
                $sapServiceLayerService = app(\App\Services\SapServiceLayerService::class);
                $testConnection = $sapServiceLayerService->testConnection($request->endpoint, $request->database, $request->username, $request->password);
                if (!$testConnection['success'] || $testConnection['status_code'] != 200) {
                    return back()->with([
                        'danger_message' => 'No se pudo establecer conexión con SAP: ' . (str_replace('`', '', $testConnection['message'])),
                        'danger_message_title' => 'ERROR DE CONEXIÓN'
                    ]);
                }
                break;

            case 'WMS':
                $request->validate([
                    'endpoint' => 'required|string|max:255',
                    'username' => 'required|string|max:255',
                    'password' => 'required|string|max:255',
                ]);

                $data = [
                    'endpoint' => $request->endpoint,
                    'username' => $request->username,
                ];

                $request->password =  $encryptionService->decrypt($request->password);
                $data['password'] = $encryptionService->encrypt($request->password);
                break;

            case 'FMMS':
                $request->validate([
                    'endpoint' => 'required|string|max:255',
                    'database' => 'required|string|max:255',
                    'username' => 'required|string|max:255',
                    'password' => 'required|string|max:255',
                    'api_key'  => 'required|string|max:255',
                ]);

                $data = [
                    'endpoint' => $request->endpoint,
                    'database' => $request->database,
                    'username' => $request->username,
                ];

                $request->password =  $encryptionService->decrypt($request->password);
                $data['password'] = $encryptionService->encrypt($request->password);

                $request->api_key =  $encryptionService->decrypt($request->api_key);
                $data['api_key'] = $encryptionService->encrypt($request->api_key);
                break;

            case 'QUERY_API':
                $request->validate([
                    'endpoint' => 'required|string|max:255',
                    'api_key'  => 'required|string|max:255',
                ]);

                $data = [
                    'endpoint' => $request->endpoint,
                ];

                $request->api_key =  $encryptionService->decrypt($request->api_key);
                $data['api_key'] = $encryptionService->encrypt($request->api_key);

                $queryApiService = app(\App\Services\QueryApiService::class);
                $testConnection = $queryApiService->executeQuery("SELECT TOP 1 * FROM \"OITM\"", $request->endpoint, $request->api_key);
                if (!$testConnection['success'] || $testConnection['status_code'] != 200) {
                    return back()->with([
                        'danger_message' => 'No se pudo establecer conexión con API: ' . $testConnection['body']['message'],
                        'danger_message_title' => 'ERROR DE CONEXIÓN'
                    ]);
                }
                break;
            case 'SQL_POS':
                $request->validate([
                    'endpoint' => 'required|string|max:255',
                    'port'     => 'required|integer',
                    'database' => 'required|string|max:255',
                    'username' => 'required|string|max:255',
                    'password' => 'required|string|max:255',
                ]);

                $data = [
                    'endpoint' => $request->endpoint,
                    'port' => $request->port ?? 1433,
                    'database' => $request->database,
                    'username' => $request->username,
                ];
                $request->password =  $encryptionService->decrypt($request->password);
                $data['password'] = $encryptionService->encrypt($request->password);

                //test de conexión
                $sqlPosService = app(\App\Services\SqlPosService::class);
                $testConnection = $sqlPosService->testConnection($request->endpoint, $request->port, $request->database, $request->username, $request->password);
                if (!$testConnection['success']) {
                    return back()->with([
                        'danger_message' => (str_replace('`', '', $testConnection['message'])),
                        'danger_message_title' => 'ERROR DE CONEXIÓN'
                    ]);
                }
                break;

            default:
                return back()->with([
                    'danger_message' => 'Registro No existe o fue Eliminado',
                    'danger_message_title' => 'ERROR DE VALIDACIÓN'
                ]);
        }

        // update o create
        if ($connectionData) {
            $update = $connectionData->update($data);
        } else {
            $data['software'] = $integration;
            $data['status'] = true;
            $update = ApiConnection::create($data);
        }
        if ($update) {
            return redirect(route('api-connections'))->with([
                'success_message' => 'Conexión actualizada Correctamente',
                'success_message_title' => 'GESTIÓN DE CONEXIONES'
            ]);
        } else {
            return back()->with([
                'danger_message' => 'Error al actualizar conexión',
                'danger_message_title' => 'ERROR DE SISTEMA'
            ]);
        }
    }
}
