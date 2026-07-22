<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(private readonly array $validationErrors)
    {
        parent::__construct('Validation failed.', 422);
    }

    public function errors(): array
    {
        return $this->validationErrors;
    }
}
