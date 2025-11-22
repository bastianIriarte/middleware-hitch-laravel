<?php

namespace App\Http\Middleware;

use Closure;
use App\Cliente;
use App\User;
use App\Entrevista;
use App\AutorizacionBaja;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;

class notClient
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
			return redirect('/');
		}else{
			return $next($request);
		}
	}

}
