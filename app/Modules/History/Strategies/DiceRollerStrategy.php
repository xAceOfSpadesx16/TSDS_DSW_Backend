<?php

namespace App\Modules\History\Strategies;

/** Valida payload y result del módulo de dados. */
class DiceRollerStrategy extends RuleBasedHistoryStrategy
{
    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'dice';
    }

    /** Reglas de validación del módulo de dados. */
    protected function rules(): array
    {
        return [
            'payload.sides' => ['required', 'integer:strict', 'min:2'],
            'payload.modifier' => ['required', 'integer:strict'],
            'payload.count' => ['sometimes', 'required', 'integer:strict', 'min:1'],
            'payload.formula' => ['sometimes', 'string'],
            'result.rolls' => ['required', 'array', 'min:1'],
            'result.rolls.*' => ['integer:strict'],
            'result.total' => ['required', 'integer:strict'],
            'result.modifier' => ['required', 'integer:strict'],
        ];
    }
}
