<?php

declare(strict_types=1);

namespace Tests\Unit\Importing\Parsers;

use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
use App\Importing\Exceptions\ParseException;
use App\Importing\Parsers\GlobalTechParser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

    public function test_attaches_pricing_sheet_rows_to_their_product_in_order(): void
    {
        $dtos = iterator_to_array((new GlobalTechParser)->parse(self::FIXTURE), false);

        $this->assertEquals(
            [
                new ImportedPriceDTO(minQuantity: 1, price: 5.50, currency: 'USD'),
                new ImportedPriceDTO(minQuantity: 100, price: 4.75, currency: 'USD'),
            ],
            $dtos[0]->prices,
        );
        $this->assertEquals(
            [new ImportedPriceDTO(minQuantity: 1, price: 12.00, currency: 'USD')],
            $dtos[1]->prices,
        );
    }

    public function test_throws_parse_exception_with_row_when_country_code_is_invalid(): void
    {
        $tmp = $this->buildSpreadsheetWithBadCountry('usa');

        try {
            iterator_to_array((new GlobalTechParser)->parse($tmp), false);
            $this->fail('Expected ParseException was not thrown.');
        } catch (ParseException $e) {
            $this->assertSame(2, $e->row);
            $this->assertMatchesRegularExpression('/country/i', $e->getMessage());
            $this->assertStringContainsString('usa', $e->getMessage());
        }
    }

    private function buildSpreadsheetWithBadCountry(string $code): string
    {
        $spreadsheet = new Spreadsheet;
        $products = $spreadsheet->getActiveSheet();
        $products->setTitle('Products');
        $products->fromArray([['Part Number', 'Brand'], ['GT-100', 'TechCo']], null, 'A1');

        $pricing = $spreadsheet->createSheet();
        $pricing->setTitle('Pricing');
        $pricing->fromArray([
            ['Part Number', 'Min Qty', 'Price', 'Currency'],
            ['GT-100', 1, 5.50, 'USD'],
        ], null, 'A1');

        $taxes = $spreadsheet->createSheet();
        $taxes->setTitle('Taxes');
        $taxes->fromArray([
            ['Part Number', 'Country', 'Unit', 'Type', 'Rate', 'Amount', 'Currency'],
            ['GT-100', $code, 'unit', 'Rate', 7.5, null, null],
        ], null, 'A1');

        $tmp = tempnam(sys_get_temp_dir(), 'gt_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        return $tmp;
    }

    public function test_attaches_taxes_sheet_rows_with_rate_or_amount_to_their_product(): void
    {
        $dtos = iterator_to_array((new GlobalTechParser)->parse(self::FIXTURE), false);

        $this->assertEquals(
            [
                new ImportedTaxDTO(countryCode: 'US', type: 'percentage', rate: 7.5, amount: null, currency: null),
                new ImportedTaxDTO(countryCode: 'ES', type: 'percentage', rate: 21.0, amount: null, currency: null),
            ],
            $dtos[0]->taxes,
        );
        $this->assertEquals(
            [new ImportedTaxDTO(countryCode: 'FR', type: 'fixed', rate: null, amount: 1.50, currency: 'EUR')],
            $dtos[1]->taxes,
        );
        $this->assertEquals(
            [
                new ImportedTaxDTO(countryCode: 'ES', type: 'percentage', rate: 21.0, amount: null, currency: null),
                new ImportedTaxDTO(countryCode: 'US', type: 'fixed', rate: null, amount: 5.00, currency: 'USD'),
            ],
            $dtos[2]->taxes,
        );
    }
}
