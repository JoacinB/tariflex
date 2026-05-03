<?php

declare(strict_types=1);

namespace App\Importing\Exceptions;

use RuntimeException;

final class ParserNotFoundException extends RuntimeException
{
    public function __construct(public readonly string $supplierCode)
    {
        parent::__construct("No parser registered for supplier code [{$supplierCode}].");
    }
}
