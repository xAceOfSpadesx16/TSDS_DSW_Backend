<?php

namespace App\Modules\History\Strategies;

/** Valida payload y result del sorteo ponderado. */
class WeightedDrawStrategy extends RuleBasedHistoryStrategy
{
    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'weighted';
    }

    /** Reglas de validación del sorteo ponderado. */
    protected function rules(): array
    {
        return [
            'payload.entries' => ['required', 'array', 'min:1'],
            'payload.entries.*' => ['array'],
            'payload.entries.*.name' => ['required', 'string', 'min:1'],
            'payload.entries.*.weight' => ['required', 'numeric:strict', 'gt:0'],
            'payload.autoRemove' => ['sometimes', 'boolean:strict'],
            'result.winner' => ['present', 'array'],
            'result.winner.name' => ['required', 'string', 'min:1'],
            'result.winner.weight' => ['required', 'numeric:strict'],
            'result.probability' => ['required', 'numeric:strict', 'between:0,100'],
        ];
    }
}
