<?php

declare(strict_types=1);

namespace App\Importing\Contracts;

use App\Importing\DTOs\ImportedProductDTO;

interface SupplierParser
{
    /**
     * @return iterable<ImportedProductDTO>
     */
    public function parse(string $filePath): iterable;
}
