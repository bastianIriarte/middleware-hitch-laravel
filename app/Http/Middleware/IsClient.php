<?php

namespace App\Http\Middleware;

use Closure;
use App\Cliente;
use App\User;
use App\Entrevista;
use App\AutorizacionBaja;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;

class IsClient
{
	/**
	 * Run the request filter.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$rol = auth()->user()->id_rol;
		if ($rol == 3){
			
			return $next($request);
		}else{
			return redirect('/dashboard')->with(['danger_message' => 'Este acceso es solo para Clientes'])->with(['danger_message_title' => 'Error de Permisos']);
		}
	}

}
