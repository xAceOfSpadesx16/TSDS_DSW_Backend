<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\History\Models\HistoryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Cobertura del borrado de historial.
 *
 * Casos verificados:
 *   1) DELETE /api/history/{id} borra un registro propio → 204.
 *   2) DELETE /api/history/{id} sobre un registro ajeno → 404 (aislamiento).
 *   3) DELETE /api/history/{id} sin Bearer token → 401.
 *   4) DELETE /api/histories borra los propios y deja intactos los ajenos.
 *   5) DELETE /api/histories sin Bearer token → 401.
 */
class HistoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $name, string $email): array
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'super-secret',
        ]);

        return [$user, JWTAuth::fromUser($user)];
    }

    private function makeRecord(int $userId, string $module = 'dice', string $action = 'roll'): HistoryRecord
    {
        return HistoryRecord::create([
            'user_id' => $userId,
            'module_name' => $module,
            'action' => $action,
            'payload' => ['sides' => 6, 'modifier' => 0],
            'result' => ['rolls' => [4], 'modifier' => 0, 'total' => 4],
        ]);
    }

    /**
     * Autentica la siguiente request con el token dado.
     *
     * Hay que olvidar los guards antes de cada request: `JWTGuard::user()`
     * cachea el usuario ya resuelto (vendor/tymon/jwt-auth/src/JWTGuard.php:71-72)
     * y el contenedor de la aplicación se reutiliza entre requests dentro de
     * un mismo test, así que sin este reset la segunda request seguiría
     * autenticada con el usuario de la primera.
     */
    private function asToken(string $token): self
    {
        $this->resetAuthState();

        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    /**
     * Descarta el estado de autenticación cacheado entre requests.
     *
     * Dentro de un mismo test la aplicación no se reconstruye, así que
     * quedan cacheados:
     *   - el token ya parseado, en el singleton `tymon.jwt`
     *     (vendor/tymon/jwt-auth/src/JWT.php:158-166);
     *   - el usuario ya resuelto, en el JWTGuard
     *     (vendor/tymon/jwt-auth/src/JWTGuard.php:71-72).
     * Sin descartarlos, la segunda request se seguiría autenticando con el
     * token y el usuario de la primera.
     */
    private function resetAuthState(): void
    {
        $this->app->forgetInstance('tymon.jwt');
        $this->app->forgetInstance('tymon.jwt.manager');
        $this->app['auth']->forgetGuards();
    }

    /**
     * El usuario borra un registro propio: 204 y la fila desaparece.
     */
    public function test_destroy_deletes_own_record(): void
    {
        [$ada, $adaToken] = $this->makeUser('Ada', 'ada@example.com');
        $record = $this->makeRecord($ada->getKey());

        $this->asToken($adaToken)
            ->deleteJson("/api/history/{$record->getKey()}")
            ->assertNoContent();

        $this->assertDatabaseMissing('history_records', ['id' => $record->getKey()]);
    }

    /**
     * Un registro de otro usuario no se puede borrar: 404 y la fila sigue.
     * El mismo endpoint sí borra el propio con 204.
     */
    public function test_destroy_rejects_another_users_record(): void
    {
        [$ada, $adaToken] = $this->makeUser('Ada', 'ada@example.com');
        [$other, $otherToken] = $this->makeUser('Other', 'other@example.com');
        $record = $this->makeRecord($other->getKey());

        // Ada intenta borrar un registro de Other → 404.
        $this->asToken($adaToken)
            ->deleteJson("/api/history/{$record->getKey()}")
            ->assertStatus(404);

        // Un id inexistente responde el mismo 404.
        $this->asToken($adaToken)
            ->deleteJson('/api/history/00000000-0000-4000-8000-000000000000')
            ->assertStatus(404);

        // Un id con formato no-UUID tampoco llega al controller: la ruta
        // declara `whereUuid('id')`, así que no matchea y da 404.
        $this->asToken($adaToken)
            ->deleteJson('/api/history/no-es-un-uuid')
            ->assertStatus(404);

        $this->assertDatabaseHas('history_records', ['id' => $record->getKey()]);

        // El dueño sí puede borrarlo → 204.
        $this->asToken($otherToken)
            ->deleteJson("/api/history/{$record->getKey()}")
            ->assertNoContent();

        $this->assertDatabaseMissing('history_records', ['id' => $record->getKey()]);
    }

    /**
     * Sin Bearer token el middleware auth:api debe rechazar con 401.
     */
    public function test_destroy_rejects_request_without_token(): void
    {
        [$ada] = $this->makeUser('Ada', 'ada@example.com');
        $record = $this->makeRecord($ada->getKey());

        $this->deleteJson("/api/history/{$record->getKey()}")->assertStatus(401);

        $this->assertDatabaseHas('history_records', ['id' => $record->getKey()]);
    }

    /**
     * "Limpiar todo" borra los registros del usuario autenticado y deja
     * intactos los de los demás.
     */
    public function test_clear_deletes_only_own_records(): void
    {
        [$ada, $adaToken] = $this->makeUser('Ada', 'ada@example.com');
        [$other, $otherToken] = $this->makeUser('Other', 'other@example.com');

        $adaRecord = $this->makeRecord($ada->getKey());
        $otherRecord = $this->makeRecord($other->getKey());

        $this->asToken($adaToken)
            ->deleteJson('/api/histories')
            ->assertNoContent();

        $this->assertDatabaseMissing('history_records', ['id' => $adaRecord->getKey()]);
        $this->assertDatabaseHas('history_records', ['id' => $otherRecord->getKey()]);

        // El usuario ajeno conserva su historial.
        $this->asToken($otherToken)
            ->getJson('/api/histories')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $otherRecord->getKey());
    }

    public function test_clear_rejects_request_without_token(): void
    {
        [$ada] = $this->makeUser('Ada', 'ada@example.com');
        $record = $this->makeRecord($ada->getKey());

        $this->deleteJson('/api/histories')->assertStatus(401);

        $this->assertDatabaseHas('history_records', ['id' => $record->getKey()]);
    }
}
