<?php

declare(strict_types=1);

namespace App\Importing\DTOs;

final class ImportedProductDTO
{
    /**
     * @param  list<ImportedPriceDTO>  $prices
     * @param  list<ImportedTaxDTO>  $taxes
     */
    public function __construct(
        public string $supplierReference,
        public string $brand,
        public array $prices = [],
        public array $taxes = [],
    ) {}
}
