<?php

namespace App\Modules\History;

use App\Models\User;
use App\Modules\History\Models\HistoryRecord;
use App\Modules\History\Strategies\HistoryStrategyFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Persistencia y validación del historial por usuario. */
class HistoryService
{
    public function __construct(
        private readonly HistoryStrategyFactory $strategies,
    ) {
    }

    /**
     * Valida y persiste un registro para el usuario.
     *
     * @param  array{module_name: string, action: string, payload: array, result: array}  $data
     *
     * @throws \App\Modules\History\Exceptions\InvalidHistoryPayloadException
     * @throws \App\Modules\History\Exceptions\UnsupportedModuleException
     */
    public function store(User $user, array $data): HistoryRecord
    {
        $strategy = $this->strategies->make($data['module_name']);

        $strategy->validate($data['payload'], $data['result']);

        return HistoryRecord::create([
            'user_id' => $user->getKey(),
            'module_name' => $data['module_name'],
            'action' => $data['action'],
            'payload' => $data['payload'],
            'result' => $data['result'],
        ]);
    }

    /** Lista el historial del usuario, del más reciente al más viejo. */
    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return HistoryRecord::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /** Borra un registro propio; `false` si no existe o es de otro usuario. */
    public function deleteForUser(User $user, string $id): bool
    {
        return HistoryRecord::query()
            ->whereKey($id)
            ->where('user_id', $user->getKey())
            ->delete() > 0;
    }

    /** Borra todo el historial del usuario. */
    public function clearForUser(User $user): void
    {
        HistoryRecord::query()
            ->where('user_id', $user->getKey())
            ->delete();
    }
}