<?php

namespace App\Modules\History\Strategies;

/** Valida payload y result de la ruleta. */
class RouletteStrategy extends RuleBasedHistoryStrategy
{
    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'roulette';
    }

    /** Reglas de validación de la ruleta. */
    protected function rules(): array
    {
        return [
            'payload.options' => ['required', 'array', 'min:1'],
            'payload.options.*' => ['array'],
            'payload.options.*.label' => ['required', 'string', 'min:1'],
            'payload.options.*.color' => ['required', 'string', 'min:1'],
            'result.winner' => ['present', 'array'],
            'result.winner.label' => ['required', 'string', 'min:1'],
            'result.winner.color' => ['required', 'string', 'min:1'],
        ];
    }
}
