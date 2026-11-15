<?php

namespace App\Modules\History\Exceptions;

use RuntimeException;

/** El `module_name` recibido no tiene estrategia registrada. */
class UnsupportedModuleException extends RuntimeException
{
    /** Crea la excepción para el módulo recibido. */
    public static function forModule(string $moduleName): self
    {
        return new self(sprintf(
            "El módulo '%s' no está soportado por el sistema de historial.",
            $moduleName,
        ));
    }
}