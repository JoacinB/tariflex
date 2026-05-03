<?php

declare(strict_types=1);

namespace App\Importing\Exceptions;

use RuntimeException;
use Throwable;

final class ParseException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $row = null, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
