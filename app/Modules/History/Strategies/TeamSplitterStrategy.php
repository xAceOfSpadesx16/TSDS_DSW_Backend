<?php

namespace App\Modules\History\Strategies;

/** Valida payload y result del generador de equipos. */
class TeamSplitterStrategy extends RuleBasedHistoryStrategy
{
    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'teams';
    }

    /** Reglas de validación del generador de equipos. */
    protected function rules(): array
    {
        return [
            'payload.participants' => ['required', 'array', 'min:1'],
            'payload.participants.*' => ['required', 'string', 'min:1'],
            'payload.mode' => ['required', 'string', 'in:count,size'],
            'payload.value' => ['required', 'integer:strict', 'min:1'],
            'payload.exclusions' => ['sometimes', 'array'],
            'payload.exclusions.*' => ['array', 'size:2'],
            'payload.exclusions.*.*' => ['required', 'string', 'min:1'],
            'result.teams' => ['required', 'array', 'min:1'],
            'result.teams.*' => ['array'],
            'result.teams.*.*' => ['string'],
            'result.colors' => ['present', 'array'],
            'result.colors.*' => ['required', 'string', 'min:1'],
        ];
    }
}
