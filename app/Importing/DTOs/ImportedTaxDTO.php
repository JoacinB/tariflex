<?php

declare(strict_types=1);

namespace App\Importing\DTOs;

final class ImportedTaxDTO
{
    public function __construct(
        public string $countryCode,
        public string $type,
        public ?float $rate,
        public ?float $amount,
        public ?string $currency,
    ) {}
}
