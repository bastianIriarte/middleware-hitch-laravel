<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Rutas que quieres ignorar (pueden ser varias)
        $excludedPaths = [
            'api/documentation',  // ruta por defecto swagger
            'vendor/l5-swagger',
            'swagger',
        ];

        // Si la request coincide con alguna ruta excluida, pasa directo sin modificar cabeceras
        foreach ($excludedPaths as $path) {
            if ($request->is($path) || $request->is($path.'/*')) {
                return $next($request);
            }
        }

        $nonce = bin2hex(random_bytes(16)); // Generar un nonce aleatorio
        app()->instance('csp_nonce', $nonce);
    
        $response = $next($request);
        return $response;
    }
}

