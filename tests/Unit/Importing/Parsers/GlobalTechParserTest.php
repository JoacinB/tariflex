<?php

declare(strict_types=1);

namespace Tests\Unit\Importing\Parsers;

use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\Parsers\GlobalTechParser;
use PHPUnit\Framework\TestCase;

final class GlobalTechParserTest extends TestCase
{
    private const FIXTURE = __DIR__.'/../../../Fixtures/Excels/globaltech.xlsx';

    public function test_parses_each_product_sheet_row_into_a_dto_with_reference_and_brand(): void
    {
        $dtos = iterator_to_array((new GlobalTechParser)->parse(self::FIXTURE), false);

        $this->assertCount(3, $dtos);
        $this->assertContainsOnlyInstancesOf(ImportedProductDTO::class, $dtos);
        $this->assertSame(['GT-100', 'GT-200', 'GT-300'], array_map(fn (ImportedProductDTO $d) => $d->supplierReference, $dtos));
        $this->assertSame(['TechCo', 'TechCo', 'OmegaCorp'], array_map(fn (ImportedProductDTO $d) => $d->brand, $dtos));
    }
}
