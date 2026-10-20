<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Cobertura de `POST /api/auth/refresh`.
 *
 * Casos verificados:
 *   1) Token válido devuelve uno nuevo, y ese nuevo sirve para `/auth/me`.
 *   2) Sin token → 401.
 *   3) Token inválido (firma que no corresponde) → 401.
 *   4) Token vencido fuera de la ventana de refresh → 401.
 *   5) Token vencido dentro de la ventana de refresh → 200 y token nuevo usable.
 */
class AuthRefreshTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'super-secret',
        ]);
    }

    public function test_refresh_con_un_token_valido_devuelve_uno_nuevo(): void
    {
        $user = $this->makeUser();
        $oldToken = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer {$oldToken}")
            ->postJson('/api/auth/refresh');

        $response->assertOk();
        $response->assertJsonPath('user.id', $user->getKey());
        $response->assertJsonPath('token_type', 'bearer');
        $this->assertIsInt($response->json('expires_in'));
        $response->assertJsonStructure(['user', 'token', 'token_type', 'expires_in']);

        $newToken = $response->json('token');
        $this->assertIsString($newToken);
        $this->assertNotSame($oldToken, $newToken, 'El refresh debe emitir un token nuevo.');
    }

    public function test_el_token_nuevo_sirve_para_auth_me(): void
    {
        $user = $this->makeUser();
        $oldToken = JWTAuth::fromUser($user);

        $newToken = $this->withHeader('Authorization', "Bearer {$oldToken}")
            ->postJson('/api/auth/refresh')
            ->assertOk()
            ->json('token');

        $this->withHeader('Authorization', "Bearer {$newToken}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->getKey());
    }

    public function test_refresh_sin_token_devuelve_401(): void
    {
        $this->postJson('/api/auth/refresh')->assertStatus(401);
    }

    public function test_refresh_con_token_invalido_devuelve_401(): void
    {
        // Firma que no corresponde a la clave del servidor.
        $this->withHeader('Authorization', 'Bearer no-es-un-jwt')
            ->postJson('/api/auth/refresh')
            ->assertStatus(401);
    }

    public function test_refresh_con_token_vencido_dentro_de_la_ventana_devuelve_200(): void
    {
        $user = $this->makeUser();

        // El token se emite con el TTL por defecto (60 min)…
        $token = JWTAuth::fromUser($user);

        // …y se viaja 2 horas: el token está vencido, pero sigue dentro de
        // `refresh_ttl` (14 días), así que se puede renovar.
        $this->travelTo(now()->addHours(2));

        $newToken = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/refresh')
            ->assertOk()
            ->json('token');

        $this->assertIsString($newToken);
        $this->assertNotSame($token, $newToken, 'El refresh debe emitir un token nuevo.');

        // El token renovado sirve para autenticar una ruta protegida.
        $this->withHeader('Authorization', "Bearer {$newToken}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->getKey());
    }

    public function test_refresh_con_token_vencido_fuera_de_la_ventana_devuelve_401(): void
    {
        $user = $this->makeUser();

        // El token se emite con el TTL por defecto (60 min)…
        $token = JWTAuth::fromUser($user);

        // …y se viaja en el tiempo más allá de la ventana de refresh
        // (`refresh_ttl = 0`): el token está vencido y no se puede renovar.
        config(['jwt.refresh_ttl' => 0]);
        $this->travelTo(now()->addDays(15));

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/refresh')
            ->assertStatus(401);
    }
}
