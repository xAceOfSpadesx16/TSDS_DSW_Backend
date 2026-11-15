<?php

namespace App\Modules\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Endpoints de autenticación; la lógica vive en {@see AuthService}. */
class AuthController extends Controller
{
    /** Inyecta las dependencias del controlador. */
    public function __construct(private readonly AuthService $auth)
    {
    }

    /** POST /api/auth/register. */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $payload = $this->auth->register($request->validated());
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'No se pudo generar el token de autenticación.',
            ], 500);
        }

        return response()->json($payload, 201);
    }

    /** POST /api/auth/login. */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $payload = $this->auth->login($request->validated());
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'No se pudo generar el token de autenticación.',
            ], 500);
        }

        if ($payload === null) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return response()->json($payload);
    }

    /** POST /api/auth/refresh, fuera de `auth:api`. */
    public function refresh(): JsonResponse
    {
        try {
            $payload = $this->auth->refresh();
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token inválido o expirado.',
            ], 401);
        }

        if ($payload === null) {
            return response()->json([
                'message' => 'Token inválido o expirado.',
            ], 401);
        }

        return response()->json($payload);
    }

    /** POST /api/auth/logout. */
    public function logout(): JsonResponse
    {
        try {
            $this->auth->logout();
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'No se pudo invalidar el token.',
            ], 500);
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /** GET /api/auth/me. */
    public function me(): JsonResponse
    {
        try {
            $user = $this->auth->me();
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token inválido o expirado.',
            ], 401);
        }

        if ($user === null) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        return response()->json([
            'user' => $user,
        ]);
    }
}