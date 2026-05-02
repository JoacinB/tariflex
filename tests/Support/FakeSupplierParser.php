<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedProductDTO;

final class FakeSupplierParser implements SupplierParser
{
    /** @param  list<ImportedProductDTO>  $dtos */
    public function __construct(public array $dtos = []) {}

    public function parse(string $filePath): iterable
    {
        yield from $this->dtos;
    }
}
