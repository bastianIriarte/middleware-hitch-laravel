<?php

namespace App\Http\Controllers;

use App\Models\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function su_register()
    {
        $email = 'root@hitch.cl';
        $password = 'admin';

        $validate_username = Users::where('username', $email)->where('deleted', false)->first();
        if (!empty($validate_username)) {
            return back()->with(['danger_message' => 'Super usuario ya está registrado'])->with(['danger_message_title' => 'ERROR DE VALIDACIÓN'])->withInput();
        }

        $user_register = new Users();
        $user_register->name =  strUpper('Super Usuario');
        $user_register->username =  $email;
        $user_register->password = bcrypt($password);
        $user_register->email =  $email;
        $user_register->profile_id = 1; #ROOT
        $user_register->created_at =  ahoraServidor();
        $user_register->validate_password = false;
        $user_register->status = true;
        $user_register->account_confirmed = true;
        $user_register->api_access = true;
        if ($user_register->save() > 0) {
            return redirect(route('login'))->with(['success_message' => 'El Super Usuario fue creado correctamente'])->with(['success_message_title' => 'USUARIO CONFIGURADO CORRECTAMENTE']);
        }

        return back()->with(['danger_message' => 'Ocurrió un error al crear la empresa'])->with(['danger_message_title' => 'ERROR INTERNO'])->withInput();
    }
}
