<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Services\BrevoService\BrevoMailer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.users')->except('logout');
    }
    public function index()
    {
        $sidenav = 'users';
        $sidenav_item = 'users_list';
        $title = 'Gestión de Usuarios';
        $title_table = 'Listado de Usuarios';
        $list_data = Users::where('deleted', false)->where('id', '!=', 1)->get();
        return view('admin.users.users_list', compact(
            'title',
            'sidenav',
            'sidenav_item',
            'title_table',
            'list_data'
        ));
    }

    public function store(Request $request)
    {
        $this->validateData($request);

        $rut = null;
        if (!empty($request->rut)) {
            $this->validate($request, [
                'rut' => 'min:9|max:13',
            ], [
                'rut.min' => 'Rut debe tener Mínimo 9 Caracteres',
                'rut.max' => 'Rut debe tener Máximo 13 Caracteres',
            ]);

            $rut = trim($request->rut);
            $rut = str_replace('.', '', $rut);

            if (!validateRut($rut)) {
                return back()->with([
                    'danger_message' => 'Rut posee formato inválido',
                    'danger_message_title' => 'ERROR DE VALIDACIÓN'
                ])->withInput();
            }

            $validate_rut = Users::where('rut', $rut)->where('deleted', false)->first();
            if (!empty($validate_rut)) {
                return back()->with([
                    'danger_message' => 'Rut ya existe en nuestros registros',
                    'danger_message_title' => 'ERROR DE VALIDACIÓN'
                ])->withInput();
            }
        }

        $email = strLower($request->email);
        $username = $email;

        $validate_user = Users::where('username', $username)->where('deleted', false)->first();
        if (!empty($validate_user)) {
            return back()->with([
                'danger_message' => 'Correo electrónico ya existe en nuestros registros',
                'danger_message_title' => 'ERROR DE VALIDACIÓN'
            ])->withInput();
        }

        // Validación y encriptación de contraseña
        if (empty($request->password) || strlen($request->password) < 8) {
            return back()->with([
                'danger_message' => 'La contraseña debe tener al menos 8 caracteres',
                'danger_message_title' => 'ERROR DE VALIDACIÓN'
            ])->withInput();
        }

        if ($request->password !== $request->password_confirmation) {
            return back()->with([
                'danger_message' => 'Las contraseñas no coinciden',
                'danger_message_title' => 'ERROR DE VALIDACIÓN'
            ])->withInput();
        }

        $request->merge([
            'username' => $username,
            'email' => $email,
            'password' => bcrypt($request->password),
            'activation_token' => null,
        ]);

        $user_register = $this->createOrUpdate($request, '');

        if ($user_register > 0) {
            return redirect(route('users'))->with([
                'success_message' => 'Usuario Creado Correctamente',
                'success_message_title' => 'GESTIÓN DE USUARIOS'
            ]);
        }

        return back()->with([
            'danger_message' => 'Ha Ocurrido un error al crear. Intente Nuevamente',
            'danger_message_title' => 'ERROR INTERNO'
        ])->withInput();
    }

    public function update(Request $request, $register_id)
    {
        $register_data = $this->validateExists($register_id);
        if (empty($register_data)) {
            return redirect(route('users'))->with(['danger_message' => 'Registro No existe o fue Eliminado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }
        $this->validateData($request);

        $rut = null;
        if (!empty($request->rut)) {
            $this->validate($request, [
                'rut' => 'min:9|max:13',
            ], [
                'rut.min' => 'Rut debe tener Mínimo 9 Caracteres',
                'rut.max' => 'Rut debe tener Máximo 13 Caracteres',
            ]);
            $rut = trim($request->rut);
            $rut = str_replace('.', '', $rut);
            if (!validateRut($rut)) {
                return back()->with(['danger_message' => 'Rut posee formato inválido'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN'])->withInput();
            }

            $validate_rut = Users::where('rut', $rut)->where('deleted', false)->where('id', '!=', $register_id)->first();
            if (!empty($validate_rut)) {
                return back()->with(['danger_message' => 'Rut ya existe en nuestros registros'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN'])->withInput();
            }
        }

        $email = strLower($request->email);
        $validate_username = Users::where('username', $email)->where('deleted', false)->where('id', '!=', $register_id)->first();
        $validate_email = Users::where('email', $email)->where('deleted', false)->where('id', '!=', $register_id)->first();
        if (!empty($validate_username) || !empty($validate_email)) {
            return back()->with(['danger_message' => 'Correo electrónico ya existe en nuestros registros'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN'])->withInput();
        }

        $register_data = $this->createOrUpdate($request, $register_data);
        if ($register_data > 0) {
            return redirect(route('users'))->with(['success_message' => 'Usuario Modificado Correctamente'])->with(['success_message_title' => 'GESTIÓN DE USUARIOS']);
        }
        return back()->with(['danger_message' => 'Ha Ocurrido un error al modificar. Intente Nuevamente'])->with(['danger_message_title' => 'ERROR INTERNO'])->withInput();
    }

    private function validateData(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email',
            'rut' => 'required',
            'api_access' => 'integer|between:0,1'

        ], [
            'name.required' => 'Nombre completo Requerido',
            'name.min' => 'Nombre completo debe tener Mínimo 3 Caracteres',
            'name.max' => 'Nombre completo debe tener Máximo 100 Caracteres',
            'api_access.integer' => 'Valor no válido para acceso api',
            'api_access.between' => 'Valor no válido para acceso api',

            'email.required' => 'Correo electrónico Requerido',
            'email.email' => ' Correo electrónico debe ser un correo válido',
            'rut.required' => 'Rut Requerido',
            'rut.required' => 'Área Requerido',

        ]);
    }

    private function validateExists($register_id)
    {
        $error = false;
        if (!is_numeric($register_id)) {
            $error = true;
        }

        $register_data = [];
        if (!$error) {
            $register_data = Users::where('deleted', 0)->where('id', $register_id)->first();
            if (empty($register_data)) {
                $error = true;
            }
        }

        return $register_data;
    }

    private function createOrUpdate(Request $request, $register_data = '')
    {
        try {
            if (empty($register_data)) {
                $register_data = new Users();
                $register_data->username = strLower($request->email);
                $register_data->created_at =  ahoraServidor();
                $register_data->user_created = auth()->user()->id;
                $register_data->password = bcrypt($request->password);
                $register_data->activation_token = $request->activation_token;
                $register_data->validate_password = true;
            }
            $register_data->name =  $request->name;
            $register_data->email = strLower($request->email);
            $register_data->rut = str_replace('.', '', $request->rut);
            $register_data->status = isset($request->status) ? ($request->status == 1 ? true : false) : true;
            $register_data->api_access = $request->api_access;
            $register_data->updated_at =  ahoraServidor();
            $register_data->user_updated = auth()->user()->id;
            return $register_data->save() ? $register_data->id : 0;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function destroy(Request $request)
    {
        // pre_die($request->all());
        $this->validate($request, [
            'id_modal' => 'required',
        ], [
            'id_modal.required' => 'Id de registro Requerido',
        ]);

        $register_data = $this->validateExists($request->id_modal);
        if (empty($register_data)) {
            return redirect(route('users'))->with(['danger_message' => 'Registro No existe o fue Eliminado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }
        if ($register_data->id == auth()->user()->id) {
            return back()->with(['danger_message' => 'Lo sentimos. No se puede eliminar su propio usuario.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }


        $register_data->deleted = true;
        $register_data->deleted_at =  ahoraServidor();
        $register_data->user_deleted = auth()->user()->id;

        if ($register_data->save()) {
            return redirect(route('users'))->with(['success_message' => 'Usuario Eliminado Correctamente'])->with(['success_message_title' => 'GESTIÓN DE USUARIOS']);
        }
        return back()->with(['danger_message' => 'Ha Ocurrido un error al eliminar. Intente Nuevamente'])->with(['danger_message_title' => 'ERROR INTERNO']);
    }

    public function restore_password(Request $request)
    {
        $this->validate($request, [
            'id_modal_restore' => 'required',
        ], [
            'id_modal_restore.required' => 'Id de registro Requerido',
        ]);
       
        $register_data = $this->validateExists($request->id_modal_restore);
        if (empty($register_data)) {
            return redirect(route('users'))->with(['danger_message' => 'Registro No existe o fue Eliminado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }
        if ($register_data->id == auth()->user()->id) {
            return back()->with(['danger_message' => 'Lo sentimos. No se puede restablecer la contraseña de tu propio usuario.'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }
        $password = generateSecurePassword();
        $register_data->password = bcrypt($password);;
        $register_data->validate_password = true;
        $register_data->updated_at =  ahoraServidor();
        $register_data->user_updated = auth()->user()->id;
        $register_data->connection_token = null;
        if ($register_data->save()) {
            #RESTABLECIMIENTO DE CONTRASEÑA
            $email = strLower($register_data->email);
            BrevoMailer::send(
                $email,
                'Restablecimiento de Contraseña - Middleware HITCH',
                'templates_email.email_restore_password_admin',
                [
                    'data' =>
                    [
                        'user_restore' => auth()->user()->name,
                        'name' => $register_data->name,
                        'password' => $password
                    ]
                ],
            );
            return redirect(route('users'))->with(['success_message' => 'Contraseña Restablecida Correctamente'])->with(['success_message_title' => 'GESTIÓN DE USUARIOS']);
        }
        return back()->with(['danger_message' => 'Ha Ocurrido un error al eliminar. Intente Nuevamente'])->with(['danger_message_title' => 'ERROR INTERNO']);
    }

    public function confirm_account(Request $request)
    {
        $this->validate($request, [
            'id_modal_confirm' => 'required',
        ], [
            'id_modal_confirm.required' => 'Id de registro Requerido',
        ]);

        $register_data = $this->validateExists($request->id_modal_confirm);
        if (empty($register_data)) {
            return redirect(route('users'))->with(['danger_message' => 'Registro No existe o fue Eliminado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }


        if ($register_data->account_confirmed) {
            return back()->with(['warning_message' => 'La cuenta del usuario ya está confirmada'])->with(['warning_message_title' => 'CUENTA YA CONFIRMADA']);
        }

        $register_data->account_confirmed = true;
        $register_data->user_confirmed = auth()->user()->id;
        $register_data->account_confirmed_at = ahoraServidor();
        $register_data->updated_at =  ahoraServidor();
        $register_data->user_updated = auth()->user()->id;
        if ($register_data->save()) {
            #ENVIO DE CONFIRMACIÓN DE CUENTA
            $email = strLower($register_data->email);
            BrevoMailer::send(
                $email,
                'Cuenta Confirmada - Middleware HITCH',
                'templates_email.email_confirmation_account',
                [
                    'data' =>
                    [
                        'name' => $register_data->name,
                    ]
                ],
            );
            return redirect(route('login'))->with(
                [
                    'success_message' => 'Su Cuenta ha sido confirmada exitosamente',
                    'success_message_title' => 'CUENTA CONFIRMADA'
                ]
            );
        }

        if ($register_data->save()) {
            return redirect(route('users'))->with(['success_message' => 'Usuario Eliminado Correctamente'])->with(['success_message_title' => 'GESTIÓN DE USUARIOS']);
        }
        return back()->with(['danger_message' => 'Ha Ocurrido un error al eliminar. Intente Nuevamente'])->with(['danger_message_title' => 'ERROR INTERNO']);
    }


    public function test_command(){
        echo 'test';
    }
}
