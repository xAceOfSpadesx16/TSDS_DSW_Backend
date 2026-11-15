<?php

namespace App\Modules\History\Exceptions;

use RuntimeException;

/** Payload o resultado de historial fuera del contrato del módulo. */
class InvalidHistoryPayloadException extends RuntimeException
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        private readonly array $errors,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}