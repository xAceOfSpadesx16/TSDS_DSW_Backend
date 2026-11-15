<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\History\Models\HistoryRecord;
use App\Modules\History\Strategies\HistoryStrategyFactory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Verifica que los datos de demostración se puedan sembrar desde cero y que
 * cada registro de historial respete el contrato de su módulo.
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_conserva_el_usuario_de_smoke_test(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_crea_las_cuentas_demo_con_la_contrasena_hasheada_por_el_modelo(): void
    {
        foreach (['demo1@thrive.test', 'demo2@thrive.test'] as $email) {
            $user = User::where('email', $email)->first();

            $this->assertNotNull($user, "Falta la cuenta {$email}.");
            $this->assertNotSame('demo12345', $user->password);
            $this->assertTrue(Hash::check('demo12345', $user->password));
        }
    }

    public function test_cada_cuenta_demo_tiene_entre_cinco_y_diez_registros(): void
    {
        foreach (['demo1@thrive.test', 'demo2@thrive.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $count = HistoryRecord::where('user_id', $user->id)->count();

            $this->assertGreaterThanOrEqual(5, $count, "{$email} tiene menos de 5 registros.");
            $this->assertLessThanOrEqual(10, $count, "{$email} tiene más de 10 registros.");
        }
    }

    public function test_cada_cuenta_demo_cubre_los_cinco_modulos(): void
    {
        $expected = ['dice', 'numbers', 'roulette', 'teams', 'weighted'];

        foreach (['demo1@thrive.test', 'demo2@thrive.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $modules = HistoryRecord::where('user_id', $user->id)
                ->pluck('module_name')
                ->unique()
                ->sort()
                ->values()
                ->all();

            $this->assertSame($expected, $modules, "{$email} no cubre los cinco módulos.");
        }
    }

    public function test_los_registros_se_reparten_en_varios_dias(): void
    {
        foreach (['demo1@thrive.test', 'demo2@thrive.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $days = HistoryRecord::where('user_id', $user->id)
                ->get()
                ->map(fn (HistoryRecord $record): string => $record->created_at->toDateString())
                ->unique();

            $this->assertGreaterThanOrEqual(2, $days->count(), "{$email} concentra todo en un día.");
        }
    }

    public function test_cada_registro_pasa_la_validacion_de_su_estrategia(): void
    {
        $strategies = app(HistoryStrategyFactory::class);
        $records = HistoryRecord::all();

        $this->assertGreaterThan(0, $records->count());

        foreach ($records as $record) {
            $strategies
                ->make($record->module_name)
                ->validate($record->payload, $record->result);

            $this->addToAssertionCount(1);
        }
    }
}
