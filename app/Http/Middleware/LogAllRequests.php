<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogAllRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Log TODAS las peticiones que llegan
        if ($request->is('api/*')) {
            Log::info('>>> PETICIÓN API RECIBIDA <<<', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'has_auth_header' => $request->hasHeader('Authorization'),
                'auth_header' => $request->hasHeader('Authorization') ? substr($request->header('Authorization'), 0, 20) . '...' : null,
                'content_type' => $request->header('Content-Type'),
            ]);
        }

        return $next($request);
    }
}
