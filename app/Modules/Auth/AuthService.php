<?php

namespace App\Modules\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

/** Registro, login, refresh, logout y usuario actual con JWT. */
class AuthService
{
    /**
     * Crea un usuario y emite su token.
     *
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: User, token: string, token_type: string, expires_in: int}
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->respondWithToken(JWTAuth::fromUser($user), $user);
    }

    /**
     * Autentica credenciales; `null` si son inválidas.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User, token: string, token_type: string, expires_in: int}|null
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function login(array $credentials): ?array
    {
        $token = Auth::guard('api')->attempt($credentials);

        if (! $token) {
            return null;
        }

        return $this->respondWithToken($token, Auth::guard('api')->user());
    }

    /**
     * Renueva el token dentro de la ventana `refresh_ttl`.
     *
     * @return array{user: User, token: string, token_type: string, expires_in: int}|null
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function refresh(): ?array
    {
        $token = JWTAuth::getToken();

        if (! $token) {
            return null;
        }

        $newToken = JWTAuth::refresh();

        // `refresh()` no autentica el guard: se resuelve el usuario con el token nuevo.
        $user = Auth::guard('api')->setToken($newToken)->user();

        if (! $user) {
            return null;
        }

        return $this->respondWithToken($newToken, $user);
    }

    /**
     * Invalida el token actual.
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    /**
     * Usuario autenticado por el token Bearer.
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     * @throws \Tymon\JWTAuth\Exceptions\TokenInvalidException
     */
    public function me(): ?User
    {
        return Auth::guard('api')->user();
    }

    /**
     * Empaqueta el token con los metadatos del contrato.
     *
     * @return array{user: User, token: string, token_type: string, expires_in: int}
     */
    private function respondWithToken(string $token, User $user): array
    {
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }
}