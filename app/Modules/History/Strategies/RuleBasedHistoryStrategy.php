<?php

namespace App\Modules\History\Strategies;

use App\Modules\History\Exceptions\InvalidHistoryPayloadException;
use Illuminate\Support\Facades\Validator;

/** Base declarativa que valida payload y result con reglas de Laravel. */
abstract class RuleBasedHistoryStrategy implements HistoryStrategyInterface
{
    /** Reglas de validación del módulo. */
    abstract protected function rules(): array;

    /** Valida payload y result con las reglas declaradas. */
    final public function validate(array $payload, array $result): void
    {
        $data = ['payload' => $payload, 'result' => $result];
        $validator = Validator::make($data, $this->applicableRules($data));

        if ($validator->fails()) {
            throw new InvalidHistoryPayloadException(
                $this->withoutCascadingErrors($validator->errors()->toArray()),
                sprintf(
                    'El payload o el resultado del módulo "%s" no cumplen con el shape esperado.',
                    $this->moduleName(),
                ),
            );
        }
    }

    /** Reglas aplicables: omite `gte` cuando la referencia no es un entero. */
    private function applicableRules(array $data): array
    {
        $rules = $this->rules();

        foreach ($rules as $attribute => $attributeRules) {
            $attributeRules = array_values(array_filter(
                $attributeRules,
                static fn (mixed $rule): bool => ! is_string($rule)
                    || ! str_starts_with($rule, 'gte:')
                    || is_int(data_get($data, substr($rule, 4))),
            ));

            $rules[$attribute] = $attributeRules === []
                ? []
                : ['bail', ...$attributeRules];
        }

        return $rules;
    }

    /** Errores sin claves descendientes de otra clave con error. */
    private function withoutCascadingErrors(array $errors): array
    {
        foreach (array_keys($errors) as $key) {
            $parent = $key;
            while (($dot = strrpos($parent, '.')) !== false) {
                $parent = substr($parent, 0, $dot);
                if (isset($errors[$parent])) {
                    unset($errors[$key]);
                    break;
                }
            }
        }

        return $errors;
    }
}
