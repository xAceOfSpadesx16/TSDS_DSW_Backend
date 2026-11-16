<?php

namespace App\Modules\History\Strategies;

/** Contrato de validación de payload y result por módulo. */
interface HistoryStrategyInterface
{
    /** Clave que el cliente envía en `module_name`. */
    public function moduleName(): string;

    /** Valida que payload y result cumplan el contrato del módulo. */
    public function validate(array $payload, array $result): void;
}
