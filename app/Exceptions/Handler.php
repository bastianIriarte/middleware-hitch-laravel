<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Personaliza la respuesta JSON para errores de validación (422)
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return \App\Helpers\ApiResponse::validationError($exception->errors());
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            // Validación (422)
            if ($exception instanceof ValidationException) {
                return $this->invalidJson($request, $exception);
            }

            // No encontrado (404)
            if ($exception instanceof NotFoundHttpException) {
                return \App\Helpers\ApiResponse::error('Recurso no encontrado', [], 404);
            }

            // No autorizado (403)
            if ($exception instanceof HttpException && $exception->getStatusCode() == 403) {
                return \App\Helpers\ApiResponse::error('No tienes permiso para acceder a este recurso', [], 403);
            }

            // No autenticado (401)
            if ($exception instanceof AuthenticationException) {
                return \App\Helpers\ApiResponse::error('No autenticado', [], 401);
            }

            // Error genérico (500 o cualquier otro)
            $code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            $message = $exception->getMessage() ?: 'Error interno del servidor';

            return \App\Helpers\ApiResponse::error($message, [], $code);
        }

        // Si no espera JSON, usar comportamiento normal
        return parent::render($request, $exception);
    }
}
