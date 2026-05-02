<?php

declare(strict_types=1);

namespace App\Importing\DTOs;

final class ImportedPriceDTO
{
    public function __construct(
        public int $minQuantity,
        public float $price,
        public string $currency,
    ) {}
}
