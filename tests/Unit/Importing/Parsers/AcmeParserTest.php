<?php

declare(strict_types=1);

namespace Tests\Unit\Importing\Parsers;

use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
use App\Importing\Exceptions\ParseException;
use App\Importing\Parsers\AcmeParser;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

    public function test_throws_parse_exception_when_required_header_is_missing(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Wrong');
        $sheet->setCellValue('B1', 'Marca');
        $sheet->setCellValue('A2', 'X-001');
        $sheet->setCellValue('B2', 'Acme');

        $tmp = tempnam(sys_get_temp_dir(), 'acme_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches('/Referencia/');

        iterator_to_array((new AcmeParser)->parse($tmp), false);
    }

    public function test_throws_parse_exception_with_row_when_price_is_non_numeric(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A1' => 'Referencia', 'B1' => 'Marca', 'H1' => 'Precio 1u'] as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->setCellValue('A2', 'X-001');
        $sheet->setCellValue('B2', 'Acme');
        $sheet->setCellValueExplicit('H2', 'not-a-number', DataType::TYPE_STRING);

        $tmp = tempnam(sys_get_temp_dir(), 'acme_').'.xlsx';
        (new Xlsx($spreadsheet))->save($tmp);

        try {
            iterator_to_array((new AcmeParser)->parse($tmp), false);
            $this->fail('Expected ParseException was not thrown.');
        } catch (ParseException $e) {
            $this->assertSame(2, $e->row);
            $this->assertMatchesRegularExpression('/(price|numeric|Precio)/i', $e->getMessage());
        }
    }

    public function test_skips_blank_tier_and_country_columns_for_minimal_row(): void
    {
        $dtos = iterator_to_array((new AcmeParser)->parse(self::FIXTURE), false);

        $this->assertEquals(
            [new ImportedPriceDTO(minQuantity: 1, price: 50.0, currency: 'EUR')],
            $dtos[1]->prices,
        );
        $this->assertEquals(
            [new ImportedTaxDTO(countryCode: 'ES', type: 'percentage', rate: 21.0, amount: null, currency: null)],
            $dtos[1]->taxes,
        );
    }
}
