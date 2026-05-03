<?php

declare(strict_types=1);

namespace App\Importing\DTOs;

final class ImportSummary
{
    /**
     * @param  list<array{row: int|null, error: string}>  $errors
     */
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public array $errors = [],
    ) {}
}
