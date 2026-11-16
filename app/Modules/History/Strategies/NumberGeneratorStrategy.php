<?php

namespace App\Modules\History\Strategies;

/** Valida payload y result del generador de números. */
class NumberGeneratorStrategy extends RuleBasedHistoryStrategy
{
    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'numbers';
    }

    /** Reglas de validación del generador de números. */
    protected function rules(): array
    {
        return [
            'payload.count' => ['required', 'integer:strict', 'min:1'],
            'payload.min' => ['required', 'integer:strict'],
            'payload.max' => ['required', 'integer:strict', 'gte:payload.min'],
            'payload.unique' => ['required', 'boolean:strict'],
            'result.numbers' => ['present', 'array'],
            'result.numbers.*' => ['integer:strict'],
        ];
    }
}
