<?php

namespace App\Modules\History\Strategies;

use App\Modules\History\Exceptions\InvalidHistoryPayloadException;

/** Valida payload y result del generador de equipos. */
class TeamSplitterStrategy implements HistoryStrategyInterface
{
    private const ALLOWED_MODES = ['count', 'size'];

    /** Nombre del módulo. */
    public function moduleName(): string
    {
        return 'teams';
    }

    /** Valida el shape del módulo. */
    public function validate(array $payload, array $result): void
    {
        $errors = [];
        if (! array_key_exists('participants', $payload)) {
            $errors['payload.participants'] = ['El campo "participants" es obligatorio.'];
        } elseif (! is_array($payload['participants']) || $payload['participants'] === []) {
            $errors['payload.participants'] = ['El campo "participants" debe ser un arreglo no vacío.'];
        } else {
            foreach ($payload['participants'] as $i => $p) {
                if (! is_string($p) || $p === '') {
                    $errors["payload.participants.$i"] = ['Cada participante debe ser un string no vacío.'];
                }
            }
        }
        if (! array_key_exists('mode', $payload)) {
            $errors['payload.mode'] = ['El campo "mode" es obligatorio.'];
        } elseif (! is_string($payload['mode']) || ! in_array($payload['mode'], self::ALLOWED_MODES, true)) {
            $errors['payload.mode'] = [
                sprintf('El campo "mode" debe ser uno de: %s.', implode(', ', self::ALLOWED_MODES)),
            ];
        }
        if (! array_key_exists('value', $payload)) {
            $errors['payload.value'] = ['El campo "value" es obligatorio.'];
        } elseif (! is_int($payload['value']) || $payload['value'] < 1) {
            $errors['payload.value'] = ['El campo "value" debe ser un entero >= 1.'];
        }
        if (array_key_exists('exclusions', $payload)) {
            if (! is_array($payload['exclusions'])) {
                $errors['payload.exclusions'] = ['El campo "exclusions" debe ser un arreglo.'];
            } else {
                foreach ($payload['exclusions'] as $i => $pair) {
                    if (! is_array($pair) || count($pair) !== 2) {
                        $errors["payload.exclusions.$i"] = ['Cada exclusión debe ser un par [a, b].'];
                        continue;
                    }
                    foreach ($pair as $j => $name) {
                        if (! is_string($name) || $name === '') {
                            $errors["payload.exclusions.$i.$j"] = ['Cada extremo de la exclusión debe ser un string no vacío.'];
                        }
                    }
                }
            }
        }
        if (! array_key_exists('teams', $result)) {
            $errors['result.teams'] = ['El campo "teams" es obligatorio.'];
        } elseif (! is_array($result['teams']) || $result['teams'] === []) {
            $errors['result.teams'] = ['El campo "teams" debe ser un arreglo no vacío.'];
        } else {
            foreach ($result['teams'] as $i => $team) {
                if (! is_array($team)) {
                    $errors["result.teams.$i"] = ['Cada equipo debe ser un arreglo.'];
                    continue;
                }
                foreach ($team as $j => $member) {
                    if (! is_string($member)) {
                        $errors["result.teams.$i.$j"] = ['Cada miembro del equipo debe ser un string.'];
                    }
                }
            }
        }
        if (! array_key_exists('colors', $result)) {
            $errors['result.colors'] = ['El campo "colors" es obligatorio.'];
        } elseif (! is_array($result['colors'])) {
            $errors['result.colors'] = ['El campo "colors" debe ser un arreglo.'];
        } else {
            foreach ($result['colors'] as $i => $color) {
                if (! is_string($color) || $color === '') {
                    $errors["result.colors.$i"] = ['Cada color debe ser un string no vacío.'];
                }
            }
        }

        if ($errors !== []) {
            throw new InvalidHistoryPayloadException(
                $errors,
                'El payload o el resultado del módulo "teams" no cumplen con el shape esperado.',
            );
        }
    }
}