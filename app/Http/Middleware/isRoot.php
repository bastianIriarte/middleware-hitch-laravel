<?php

namespace App\Http\Middleware;

use Closure;
use App\Cliente;
use App\User;
use App\Entrevista;
use App\AutorizacionBaja;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;

class isRoot
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
		if(notSuperUser()){
			return redirect(route('dashboard'))->with([
				'danger_message' => 'No tienes los permisos necesarios',
				'danger_message_title' => 'ERROR DE VALIDACIÓN'
			]);
		}
		return $next($request);
	}

}
