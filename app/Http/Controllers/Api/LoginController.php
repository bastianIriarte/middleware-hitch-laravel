<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Login de usuario"
 * )
 */
class LoginController extends Controller
{
    public function __construct()
    {
        
    }

     /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login de usuario",
     *     description="Permite a un usuario autenticarse con su username y password.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="root@hitch.cl"),
     *             @OA\Property(property="password", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User logged in successfully."),
     *             @OA\Property(property="access_token", type="string", example="your_token_here"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=15552000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario y/o Contraseña Incorrectos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuario inhabilitado o eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario deshabilitado o eliminado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recurso no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Recurso no encontrado"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-10T15:47:48-04:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={}),
     *             @OA\Property(property="timestamp", type="string", example="2025-07-10T15:47:48-04:00")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Usuario Requerido',
            'password.required' => 'Contraseña Requerida',
        ]);

        $credentials = $request->only('username', 'password');

        // Intentar autenticación con JWT
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Usuario y/o Contraseña Incorrectos'
            ], 401);
        }

        $user = auth('api')->user();

        if ($user->deleted) {
            return response()->json([
                'message' => 'Su usuario ha sido eliminado. Para más información contácte a soporte'
            ], 403);
        }

        if (!$user->status) {
            return response()->json([
                'message' => 'Su usuario ha sido deshabilitado. Para más información contácte a soporte'
            ], 403);
        }

        if (!$user->api_access) {
            return response()->json([
                'message' => 'Su cuenta no ha sido habilitada para acceder a la API. Para más información contácte a soporte'
            ], 403);
        }

        return response()->json([
            'message' => 'Autenticación exitosa',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60, // tiempo en segundos
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token inválido o expirado.'], 401);
        }

        return response()->json([
            'message' => 'Token renovado exitosamente',
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
