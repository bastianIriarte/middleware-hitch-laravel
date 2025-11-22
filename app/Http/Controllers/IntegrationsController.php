<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\IntegrationView;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class IntegrationsController extends Controller

{
    public function __construct()
    {
        $this->middleware('auth.users');
    }
    public function index($slug)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');
        if (empty($slug)) {
            return redirect(route('dashboard'))->with(['danger_message' => 'Slug de integración no puede venir vacío.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $resource = Resource::where('slug', $slug)->where('status', true)->where('show_user', true)->first();

        if (!$resource) {
            return redirect(route('dashboard'))->with(['danger_message' => 'Recurso no existe o fue eliminado.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $sidenav = 'integrations';
        $sidenav_item = 'integrations_list';
        $title = 'Gestión de Integraciones' . (!empty($resource->name) ? " de $resource->name" : '');
        $title_table = 'Listado de Integraciones' . (!empty($resource->name) ? " de $resource->name" : '');

        $list_data = IntegrationView::where('deleted', false)
            ->where('table_name', $resource->integrations_table)
            ->limit(100)->orderBy('id', 'desc')->get();

        // ->map(function ($item) {
        //     foreach (['request_body', 'create_body', 'response'] as $field) {
        //         if (!empty($item->$field) && is_string($item->$field)) {
        //             // Intenta decodificar y volver a codificar de forma pretty
        //             $decoded = json_decode($item->$field, true);
        //             if (json_last_error() === JSON_ERROR_NONE) {
        //                 $item->$field = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        //             }
        //         }
        //     }
        //     return $item;
        // });

        return view('admin.integrations.integrations_list', compact(
            'title',
            'sidenav',
            'sidenav_item',
            'title_table',
            'list_data',
            'resource'
        ));
    }

    // public function reintegrate(Request $request, $slug)
    // {
    //     if (empty($slug)) {
    //         return redirect(route('dashboard'))->with(['danger_message' => 'Slug de integración no puede venir vacío.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
    //     }

    //     $resource = Resource::where('slug', $slug)->where('status', true)->where('show_user', true)->first();

    //     if (!$resource) {
    //         return redirect(route('dashboard'))->with(['danger_message' => 'Recurso no existe o fue eliminado.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
    //     }

    //     $this->validate($request, [
    //         'id_modal' => 'required',
    //     ], [
    //         'id_modal.required' => 'Id de registro Requerido',
    //     ]);

    //     $register_data = $this->validateExists($resource->integrations_table, $request->id_modal);
    //     if (empty($register_data)) {
    //         return redirect(route('integrations', ['slug' => $resource->slug]))->with(['danger_message' => 'Registro No existe o fue Eliminado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
    //     }

    //     // Lógica de reintegración acá


    //    return redirect(route('integrations', ['slug' => $resource->slug]))->with(['success_message' => 'Reintegración generada correctamente'])->with(['success_message_title' => 'GESTIÓN DE USUARIOS']);
    // }

    public function close(Request $request, $slug)
    {
        if (empty($slug)) {
            return redirect(route('dashboard'))->with(['danger_message' => 'Slug de integración no puede venir vacío.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $resource = Resource::where('slug', $slug)->where('status', true)->where('show_user', true)->first();

        if (!$resource) {
            return redirect(route('dashboard'))->with(['danger_message' => 'Recurso no existe o fue eliminado.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $this->validate($request, [
            'id_modal' => 'required',
        ], [
            'id_modal.required' => 'Id de registro Requerido',
        ]);

        $register_data = $this->validateExists($resource->integrations_table, $request->id_modal);
        if (empty($register_data)) {
            return redirect(route('integrations', ['slug' => $resource->slug]))->with(['danger_message' => 'Registro No existe o fue Eliminado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $update = $register_data->update([
            'status_integration_id' => 5,
            'updated_at' => now(),
            'user_updated' => auth()->user()->id
        ]);

        if (!$update) {
            return redirect(route('integrations', ['slug' => $resource->slug]))->with(['danger_message' => 'Error al modificar registro, por favor intente nuevamente.'])->with(['danger_message_title' => 'ERROR DE SISTEMA']);
        }

        return redirect(route('integrations', ['slug' => $resource->slug]))->with(['success_message' => 'Integracion marcada como cerrada correctamente'])->with(['success_message_title' => 'GESTIÓN DE USUARIOS']);
    }

    private function validateExists($table_name, $register_id)
    {
        $error = false;
        if (!is_numeric($register_id)) {
            $error = true;
        }

        // Validar existencia de la tabla
        if (!Schema::hasTable($table_name)) {
            $error = true;
        }

        $register_data = [];
        if (!$error) {
            $model = new Integration();
            $model->setTableName($table_name);
            $register_data = $model->where('deleted', 0)->where('id', $register_id)->first();
            if (empty($register_data)) {
                $error = true;
            }
        }

        return $register_data;
    }
}
