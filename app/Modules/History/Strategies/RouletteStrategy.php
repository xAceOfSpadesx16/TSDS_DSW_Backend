<?php

namespace App\Modules\History\Strategies;

use App\Modules\History\Exceptions\InvalidHistoryPayloadException;

/** Valida payload y result de la ruleta. */
class RouletteStrategy implements HistoryStrategyInterface
{
    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'roulette';
    }

    /** Valida el shape del módulo. */
    public function validate(array $payload, array $result): void
    {
        $errors = [];
        if (! array_key_exists('options', $payload)) {
            $errors['payload.options'] = ['El campo "options" es obligatorio.'];
        } elseif (! is_array($payload['options']) || $payload['options'] === []) {
            $errors['payload.options'] = ['El campo "options" debe ser un arreglo no vacío.'];
        } else {
            foreach ($payload['options'] as $i => $option) {
                if (! is_array($option)) {
                    $errors["payload.options.$i"] = ['Cada opción debe ser un objeto.'];
                    continue;
                }
                if (! isset($option['label']) || ! is_string($option['label']) || $option['label'] === '') {
                    $errors["payload.options.$i.label"] = ['Cada opción debe tener un "label" string no vacío.'];
                }
                if (! isset($option['color']) || ! is_string($option['color']) || $option['color'] === '') {
                    $errors["payload.options.$i.color"] = ['Cada opción debe tener un "color" string no vacío.'];
                }
            }
        }
        if (! array_key_exists('winner', $result)) {
            $errors['result.winner'] = ['El campo "winner" es obligatorio.'];
        } elseif (! is_array($result['winner'])) {
            $errors['result.winner'] = ['El campo "winner" debe ser un objeto.'];
        } else {
            if (! isset($result['winner']['label']) || ! is_string($result['winner']['label']) || $result['winner']['label'] === '') {
                $errors['result.winner.label'] = ['"winner.label" debe ser un string no vacío.'];
            }
            if (! isset($result['winner']['color']) || ! is_string($result['winner']['color']) || $result['winner']['color'] === '') {
                $errors['result.winner.color'] = ['"winner.color" debe ser un string no vacío.'];
            }
        }

        if ($errors !== []) {
            throw new InvalidHistoryPayloadException(
                $errors,
                'El payload o el resultado del módulo "roulette" no cumplen con el shape esperado.',
            );
        }
    }
}