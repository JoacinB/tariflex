<?php

declare(strict_types=1);

namespace App\Importing\Parsers;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
use App\Importing\Exceptions\ParseException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

final class AcmeParser implements SupplierParser
{
    private const CURRENCY = 'EUR';

    private const PRICE_TIERS = [
        'H' => 1,
        'I' => 10,
        'J' => 50,
    ];

    private const TAX_COUNTRIES = [
        'K' => 'ES',
        'L' => 'FR',
    ];

    public function parse(string $filePath): iterable
    {
        try {
            $sheet = IOFactory::load($filePath)->getActiveSheet();
        } catch (ReaderException|Throwable $e) {
            throw new ParseException(
                'The file could not be read as an Excel spreadsheet: '.$e->getMessage(),
                previous: $e,
            );
        }

        foreach ($sheet->getRowIterator(2) as $row) {
            $reference = $sheet->getCell('A'.$row->getRowIndex())->getValue();

            if ($reference === null || $reference === '') {
                continue;
            }

            yield new ImportedProductDTO(
                supplierReference: (string) $reference,
                brand: (string) $sheet->getCell('B'.$row->getRowIndex())->getValue(),
                prices: $this->prices($sheet, $row->getRowIndex()),
                taxes: $this->taxes($sheet, $row->getRowIndex()),
            );
        }
    }

    /**
     * @return list<ImportedPriceDTO>
     */
    private function prices(Worksheet $sheet, int $rowIndex): array
    {
        $prices = [];
        foreach (self::PRICE_TIERS as $column => $minQuantity) {
            $value = $sheet->getCell($column.$rowIndex)->getValue();
            if ($value === null || $value === '') {
                continue;
            }
            $prices[] = new ImportedPriceDTO(
                minQuantity: $minQuantity,
                price: (float) $value,
                currency: self::CURRENCY,
            );
        }

        return $prices;
    }

    /**
     * @return list<ImportedTaxDTO>
     */
    private function taxes(Worksheet $sheet, int $rowIndex): array
    {
        $taxes = [];
        foreach (self::TAX_COUNTRIES as $column => $countryCode) {
            $value = $sheet->getCell($column.$rowIndex)->getValue();
            if ($value === null || $value === '') {
                continue;
            }
            $taxes[] = new ImportedTaxDTO(
                countryCode: $countryCode,
                type: 'percentage',
                rate: (float) $value,
                amount: null,
                currency: null,
            );
        }

        return $taxes;
    }
}
