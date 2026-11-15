<?php

namespace App\Modules\History\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de un sorteo persistido.
 *
 * @property string $id
 * @property int|null $user_id
 * @property string $module_name
 * @property string $action
 * @property array $payload
 * @property array $result
 */
class HistoryRecord extends Model
{
    use HasUuids;

    protected $table = 'history_records';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'module_name',
        'action',
        'payload',
        'result',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
        ];
    }

    /** Usuario dueño del registro. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}