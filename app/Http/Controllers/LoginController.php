<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRecoveryRequest;
use App\Models\ResetPassword;
use App\Models\Users;
use App\Services\BrevoService\BrevoMailer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except(['logout']);
    }


    public function login(Request $request)
    {
        $title = 'Inicio de Sesión';
        $redirect = isset($request->redirect) ? $request->redirect : '';
        return view('auth.login.login', compact('title', 'redirect'));
    }

    public function login_validate(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',

        ], [
            'username.required' => 'Usuario Requerido',
            'password.required' => 'Contraseña Requerida',
        ]);

        $user_data = Users::where('username', strLower($request->username))->where('deleted', false)->first();
        if (empty($user_data)) {
            return back()->with(['danger_message' => 'Usuario y/o Contraseña Incorrectos'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        if (!$user_data->status) {
            return back()->with(['warning_message' => 'Su usuario a sido deshabilitado. Para más información contácte a soporte'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        if (ALERT_MANTENIMIENTO) {
            return back()->with(['danger_message' => 'Lo sentimos, el sitio se encuentra en mantenimiento vuelva a intentarlo en otro momento.'])->with(['danger_message_title' => 'SITIO EN MANTENIMIENTO']);
        }

        if (auth()->attempt(['username' => strLower($request->username), 'password' => $request->password, 'deleted' => false]) == false) {
            return back()->with(['danger_message' => 'Usuario y/o Contraseña Incorrectos'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN']);
        }

        $user_data->connection_token = session()->getId();
        $user_data->last_entry = ahoraServidor();
        $user_data->save();
        session()->put('connection', $user_data->connection_token);
        return redirect(route('dashboard'))->with(['success_message' => 'Bienvenido nuevamente :)'])->with(['success_message_title' => 'Sesión Iniciada']);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return redirect(route('login'))->with(['success_message' => 'Hasta Luego'])->with(['success_message_title' => 'Sesión Finalizada']);
    }
}
