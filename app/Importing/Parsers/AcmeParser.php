<?php

declare(strict_types=1);

namespace App\Importing\Parsers;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class AcmeParser implements SupplierParser
{
    private const CURRENCY = 'EUR';

    private const PRICE_TIERS = [
        'H' => 1,
        'I' => 10,
        'J' => 50,
    ];

    public function parse(string $filePath): iterable
    {
        $sheet = IOFactory::load($filePath)->getActiveSheet();

        foreach ($sheet->getRowIterator(2) as $row) {
            $reference = $sheet->getCell('A'.$row->getRowIndex())->getValue();

            if ($reference === null || $reference === '') {
                continue;
            }

            yield new ImportedProductDTO(
                supplierReference: (string) $reference,
                brand: (string) $sheet->getCell('B'.$row->getRowIndex())->getValue(),
                prices: $this->prices($sheet, $row->getRowIndex()),
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
}
