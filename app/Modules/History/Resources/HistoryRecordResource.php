<?php

namespace App\Modules\History\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Serializa un registro de historial. */
class HistoryRecordResource extends JsonResource
{
    /**
     * Serializa el registro.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'module_name' => $this->module_name,
            'action' => $this->action,
            'payload' => $this->payload,
            'result' => $this->result,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}