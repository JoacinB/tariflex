<?php

declare(strict_types=1);

namespace Tests\Unit\Importing\Parsers;

use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\Parsers\AcmeParser;
use PHPUnit\Framework\TestCase;

final class AcmeParserTest extends TestCase
{
    private const FIXTURE = __DIR__.'/../../../Fixtures/Excels/acme.xlsx';

    public function test_parses_each_data_row_into_a_dto_with_reference_and_brand(): void
    {
        $dtos = iterator_to_array((new AcmeParser)->parse(self::FIXTURE), false);

        $this->assertCount(3, $dtos);
        $this->assertContainsOnlyInstancesOf(ImportedProductDTO::class, $dtos);
        $this->assertSame(['A-001', 'A-002', 'A-003'], array_map(fn (ImportedProductDTO $d) => $d->supplierReference, $dtos));
        $this->assertSame(['Acme', 'Acme', 'Globex'], array_map(fn (ImportedProductDTO $d) => $d->brand, $dtos));
    }
}
