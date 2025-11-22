<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UsersAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        if (auth()->check()) {
            if (auth()->user()->deleted) {
                auth()->logout();
                return redirect(route('login'))->with(['error_message' => 'Su cuenta ha sido eliminada'])->with(['error_message_title' => 'ERROR DE VALIDACIÓN']);
            }

            // Verificar permiso si se proporciona
            if(notSuperUser()){
                if ($permission && !auth()->user()->hasPermission($permission)) {
                    return redirect(route('dashboard'))->with([
                        'danger_message' => 'No tienes los permisos necesarios',
                        'danger_message_title' => 'ERROR DE VALIDACIÓN'
                    ]);
                }
            }
           
            if(!auth()->user()->account_confirmed){
                return redirect(route('logout'))->with(['warning_message' => 'Su cuenta aún no ha sido confirmada.'])->with(['warning_message_title' => 'Cuenta no confirmada']);
            }
            
            if(auth()->user()->validate_password){
                return redirect(route('change-password-required'))->with(['warning_message' => 'Realice el cambio de contraseña para continuar'])->with(['warning_message_title' => 'Cambio de Contraseña Obligatorio']);
            }

            if (ALERT_MANTENIMIENTO && notSuperUser()) {
                return redirect(route('logout'))->with(['error_message' => 'Lo sentimos, el sitio se encuentra en mantenimiento vuelva a intentarlo en otro momento.'])->with(['error_message_title' => 'SITIO EN MANTENIMIENTO']);
            }
            // if(empty(auth()->user()->connection_token) || auth()->user()->connection_token != session('connection')){
            //     return redirect(route('session-finish'));
            // }
            return $next($request);
        } else {
            return redirect(route('login'));
        }
    }
}
