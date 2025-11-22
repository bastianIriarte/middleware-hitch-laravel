<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MyProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.users')->except('logout');
    }
    public function profile_edit()
    {
        $title = 'Mi Perfil';
        return view('admin.my_profile', compact('title'));
    }

    public function profile_update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:100',
        ], [
            'name.required' => 'Nombre completo Requerido',
            'name.min' => 'Nombre completo debe tener Mínimo 3 Caracteres',
            'name.max' => 'Nombre completo debe tener Máximo 255 Caracteres',

            'email.required' => 'Correo electrónico Requerido',
            'email.required' => 'Correo electrónico con Formato Inválido',
            'email.max' => 'Correo electrónico debe tener Máximo 100 Caracteres',

        ]);

        $rut = null;
        if (!empty($request->rut)) {
            $this->validate($request, [
                'rut' => 'min:11|max:13',
            ], [
                'rut.min' => 'Rut debe tener Mínimo 11 Caracteres',
                'rut.max' => 'Rut debe tener Máximo 13 Caracteres',
            ]);
            $rut = trim($request->rut);
            $rut = str_replace('.', '', $rut);
            if (!validateRut($rut)) {
                return back()->with(['danger_message' => 'Rut posee formato inválido'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN'])->withInput();
            }

            $validate_rut = Users::where('rut', $rut)->where('deleted', false)->where('id', '!=', auth()->user()->id)->first();
            if (!empty($validate_rut)) {
                return back()->with(['danger_message' => 'Rut ya existe en nuestros registros'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN'])->withInput();
            }
        }

        $email = strLower($request->email);
        $validate_username = Users::where('username', $email)->where('deleted', false)->where('id', '!=', auth()->user()->id)->first();
        $validate_email = Users::where('username', $email)->where('deleted', false)->where('id', '!=', auth()->user()->id)->first();
        if (!empty($validate_username) || !empty($validate_email)) {
            return back()->with(['danger_message' => 'Correo electrónico ya existe en nuestros registros'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }
        $user_data = Users::find(auth()->user()->id);
        $user_data->email = strLower($request->email);
        $user_data->rut = $rut;
        $user_data->menu_type = $request->menu_type == 'sidebar-collapse' ? 'sidebar-collapse' : null;
        $user_data->updated_at =  ahoraServidor();
        if ($user_data->save()) {
            return redirect(route('profile'))->with(['success_message' => 'Perfil Actualizado Correctamente'])->with(['success_message_title' => 'Mi perfil']);
        }
        return back()->with(['danger_message' => 'Ha Ocurrido un error al actualizar. Intente Nuevamente'])->with(['danger_message_title' => 'Error Interno'])->withInput();
    }

    public function change_password_edit()
    {
        $title = 'Cambiar Contraseña';
        $sidenav = 'change-password';
        return view('admin.my_profile_change_password', compact('title', 'sidenav'));
    }

    public function change_password_update(Request $request)
    {
        $this->validate($request, [
            'password_current' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/'
            ],
            'password_confirm' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[\W_]/'
            ],

        ], [
            'password_current.required' => 'Contraseña Actual Requerida',
            'password.required' => 'Contraseña requerida',
            'password.string' => 'Contraseña debe ser una cadena de texto',
            'password.min' => 'Contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'Contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial',

            'password_confirm.required' => 'Confirmar Contraseña requerida',
            'password_confirm.string' => 'Confirmar Contraseña debe ser una cadena de texto',
            'password_confirm.min' => 'Confirmar Contraseña debe tener al menos 8 caracteres',
            'password_confirm.regex' => 'Confirmar Contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial',
        ]);

        if ($request->password != $request->password_confirm) {
            return back()->with(['danger_message' => 'Contraseñas deben ser Indenticas'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $user_data = Users::find(auth()->user()->id);
        if (!Hash::check($request->password_current, $user_data->password)) {
            return back()->with(['danger_message' => 'Contraseña actual es Incorrecta'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $user_data->password = bcrypt($request->password);
        $user_data->updated_at =  ahoraServidor();
        if ($user_data->save()) {
            return redirect(route('change-password'))->with(['success_message' => 'Contraseña Actualizada Correctamente'])->with(['success_message_title' => 'Mi Perfil']);
        }
        return back()->with(['danger_message' => 'Ha Ocurrido un error al modificar Contraseña. Intente Nuevamente'])->with(['danger_message_title' => 'Error Interno'])->withInput();
    }
}
