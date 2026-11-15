<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\History\Models\HistoryRecord;
use App\Modules\History\Strategies\HistoryStrategyFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Siembra los usuarios de prueba y su historial de demostración. */
    public function run(): void
    {
        $this->createUser('Test User', 'test@example.com', 'password');

        $demoOne = $this->createUser('Demo Uno', 'demo1@thrive.test', 'demo12345');
        $demoTwo = $this->createUser('Demo Dos', 'demo2@thrive.test', 'demo12345');

        $this->seedHistory($demoOne, $this->demoOneRecords());
        $this->seedHistory($demoTwo, $this->demoTwoRecords());
    }

    private function createUser(string $name, string $email, string $password): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Persiste una lista de registros validando el shape de cada módulo.
     *
     * @param  array<int, array{module_name: string, action: string, payload: array<string, mixed>, result: array<string, mixed>, created_at: Carbon}>  $records
     */
    private function seedHistory(User $user, array $records): void
    {
        $strategies = app(HistoryStrategyFactory::class);

        foreach ($records as $data) {
            $strategies->make($data['module_name'])->validate($data['payload'], $data['result']);

            $record = new HistoryRecord([
                'user_id' => $user->getKey(),
                'module_name' => $data['module_name'],
                'action' => $data['action'],
                'payload' => $data['payload'],
                'result' => $data['result'],
            ]);
            $record->created_at = $data['created_at'];
            $record->updated_at = $data['created_at'];
            $record->save();
        }
    }

    /**
     * Ocho registros de demo 1, con los cinco módulos y varias fechas.
     *
     * @return array<int, array{module_name: string, action: string, payload: array<string, mixed>, result: array<string, mixed>, created_at: Carbon}>
     */
    private function demoOneRecords(): array
    {
        return [
            [
                'module_name' => 'dice',
                'action' => 'd20+2',
                'payload' => ['sides' => 20, 'modifier' => 2],
                'result' => ['rolls' => [17], 'modifier' => 2, 'total' => 19],
                'created_at' => now()->subDays(4)->setTime(10, 15),
            ],
            [
                'module_name' => 'numbers',
                'action' => '5 números [1-50] (únicos)',
                'payload' => ['count' => 5, 'min' => 1, 'max' => 50, 'unique' => true],
                'result' => ['numbers' => [3, 11, 24, 37, 45]],
                'created_at' => now()->subDays(4)->setTime(11, 2),
            ],
            [
                'module_name' => 'roulette',
                'action' => 'Ruleta con 4 opciones',
                'payload' => [
                    'options' => [
                        ['label' => 'Pizza', 'color' => '#1f4fb5'],
                        ['label' => 'Empanadas', 'color' => '#1f6b47'],
                        ['label' => 'Sushi', 'color' => '#b45309'],
                        ['label' => 'Milanesas', 'color' => '#a32121'],
                    ],
                ],
                'result' => ['winner' => ['label' => 'Pizza', 'color' => '#1f4fb5']],
                'created_at' => now()->subDays(3)->setTime(9, 40),
            ],
            [
                'module_name' => 'teams',
                'action' => '8 participantes → 2 grupos',
                'payload' => [
                    'participants' => ['Ana', 'Beto', 'Cami', 'Diego', 'Elena', 'Fede', 'Gabi', 'Hugo'],
                    'mode' => 'count',
                    'value' => 2,
                    'exclusions' => [['Ana', 'Beto']],
                ],
                'result' => [
                    'teams' => [
                        ['Ana', 'Cami', 'Elena', 'Gabi'],
                        ['Beto', 'Diego', 'Fede', 'Hugo'],
                    ],
                    'colors' => ['#1f4fb5', '#1f6b47'],
                ],
                'created_at' => now()->subDays(3)->setTime(18, 20),
            ],
            [
                'module_name' => 'weighted',
                'action' => 'Sorteo entre 4 participantes',
                'payload' => [
                    'entries' => [
                        ['name' => 'Ana', 'weight' => 1],
                        ['name' => 'Beto', 'weight' => 2],
                        ['name' => 'Cami', 'weight' => 3],
                        ['name' => 'Diego', 'weight' => 4],
                    ],
                    'autoRemove' => false,
                ],
                'result' => [
                    'winner' => ['name' => 'Cami', 'weight' => 3],
                    'probability' => 30,
                ],
                'created_at' => now()->subDays(2)->setTime(20, 5),
            ],
            [
                'module_name' => 'dice',
                'action' => 'd6',
                'payload' => ['sides' => 6, 'modifier' => 0],
                'result' => ['rolls' => [4], 'modifier' => 0, 'total' => 4],
                'created_at' => now()->subDay()->setTime(8, 30),
            ],
            [
                'module_name' => 'numbers',
                'action' => '3 números [1-10] (únicos)',
                'payload' => ['count' => 3, 'min' => 1, 'max' => 10, 'unique' => true],
                'result' => ['numbers' => [2, 6, 9]],
                'created_at' => now()->setTime(9, 12),
            ],
            [
                'module_name' => 'weighted',
                'action' => 'Sorteo entre 4 participantes',
                'payload' => [
                    'entries' => [
                        ['name' => 'Ana', 'weight' => 2],
                        ['name' => 'Beto', 'weight' => 2],
                        ['name' => 'Cami', 'weight' => 2],
                        ['name' => 'Diego', 'weight' => 2],
                    ],
                ],
                'result' => [
                    'winner' => ['name' => 'Ana', 'weight' => 2],
                    'probability' => 25,
                ],
                'created_at' => now()->setTime(12, 45),
            ],
        ];
    }

    /**
     * Seis registros de demo 2, con los cinco módulos y varias fechas.
     *
     * @return array<int, array{module_name: string, action: string, payload: array<string, mixed>, result: array<string, mixed>, created_at: Carbon}>
     */
    private function demoTwoRecords(): array
    {
        return [
            [
                'module_name' => 'dice',
                'action' => 'd12-1',
                'payload' => ['sides' => 12, 'modifier' => -1],
                'result' => ['rolls' => [9], 'modifier' => -1, 'total' => 8],
                'created_at' => now()->subDays(3)->setTime(15, 10),
            ],
            [
                'module_name' => 'numbers',
                'action' => '2 números [1-100] (únicos)',
                'payload' => ['count' => 2, 'min' => 1, 'max' => 100, 'unique' => true],
                'result' => ['numbers' => [18, 74]],
                'created_at' => now()->subDays(3)->setTime(16, 25),
            ],
            [
                'module_name' => 'roulette',
                'action' => 'Ruleta con 3 opciones',
                'payload' => [
                    'options' => [
                        ['label' => 'Cine', 'color' => '#1f4fb5'],
                        ['label' => 'Teatro', 'color' => '#b45309'],
                        ['label' => 'Cena', 'color' => '#1f6b47'],
                    ],
                ],
                'result' => ['winner' => ['label' => 'Cine', 'color' => '#1f4fb5']],
                'created_at' => now()->subDays(2)->setTime(19, 0),
            ],
            [
                'module_name' => 'teams',
                'action' => '6 participantes → 3 grupos',
                'payload' => [
                    'participants' => ['Iris', 'Juli', 'Kari', 'Lucas', 'Mora', 'Nico'],
                    'mode' => 'count',
                    'value' => 3,
                    'exclusions' => [['Iris', 'Juli']],
                ],
                'result' => [
                    'teams' => [
                        ['Iris', 'Lucas'],
                        ['Juli', 'Mora'],
                        ['Kari', 'Nico'],
                    ],
                    'colors' => ['#1f4fb5', '#b45309', '#1f6b47'],
                ],
                'created_at' => now()->subDay()->setTime(11, 35),
            ],
            [
                'module_name' => 'weighted',
                'action' => 'Sorteo entre 3 participantes',
                'payload' => [
                    'entries' => [
                        ['name' => 'Iris', 'weight' => 5],
                        ['name' => 'Juli', 'weight' => 3],
                        ['name' => 'Kari', 'weight' => 2],
                    ],
                ],
                'result' => [
                    'winner' => ['name' => 'Iris', 'weight' => 5],
                    'probability' => 50,
                ],
                'created_at' => now()->setTime(10, 5),
            ],
            [
                'module_name' => 'dice',
                'action' => 'd8+5',
                'payload' => ['sides' => 8, 'modifier' => 5],
                'result' => ['rolls' => [3], 'modifier' => 5, 'total' => 8],
                'created_at' => now()->setTime(13, 20),
            ],
        ];
    }
}
