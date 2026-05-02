<?php

declare(strict_types=1);

namespace Tests\Unit\Importing\Parsers;

use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
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

    public function test_yields_one_price_tier_dto_per_filled_quantity_column(): void
    {
        $dtos = iterator_to_array((new AcmeParser)->parse(self::FIXTURE), false);

        $this->assertEquals(
            [
                new ImportedPriceDTO(minQuantity: 1, price: 100.0, currency: 'EUR'),
                new ImportedPriceDTO(minQuantity: 10, price: 90.0, currency: 'EUR'),
                new ImportedPriceDTO(minQuantity: 50, price: 80.0, currency: 'EUR'),
            ],
            $dtos[0]->prices,
        );
    }

    public function test_yields_a_percentage_tax_dto_per_filled_country_column(): void
    {
        $dtos = iterator_to_array((new AcmeParser)->parse(self::FIXTURE), false);

        $this->assertEquals(
            [
                new ImportedTaxDTO(countryCode: 'ES', type: 'percentage', rate: 21.0, amount: null, currency: null),
                new ImportedTaxDTO(countryCode: 'FR', type: 'percentage', rate: 20.0, amount: null, currency: null),
            ],
            $dtos[0]->taxes,
        );
    }
}
