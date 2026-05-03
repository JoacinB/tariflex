<?php

declare(strict_types=1);

namespace App\Importing\Parsers;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class GlobalTechParser implements SupplierParser
{
    public function parse(string $filePath): iterable
    {
        $spreadsheet = IOFactory::load($filePath);
        $pricesByPart = $this->pricesByPart($spreadsheet);

        $products = $spreadsheet->getSheetByName('Products');

        foreach ($products->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $reference = $products->getCell('A'.$rowIndex)->getValue();

            if ($reference === null || $reference === '') {
                continue;
            }

            $reference = (string) $reference;

            yield new ImportedProductDTO(
                supplierReference: $reference,
                brand: (string) $products->getCell('B'.$rowIndex)->getValue(),
                prices: $pricesByPart[$reference] ?? [],
            );
        }
    }

    /**
     * @return array<string, list<ImportedPriceDTO>>
     */
    private function pricesByPart(Spreadsheet $spreadsheet): array
    {
        $sheet = $spreadsheet->getSheetByName('Pricing');
        $byPart = [];

        foreach ($sheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $partNumber = $sheet->getCell('A'.$rowIndex)->getValue();

            if ($partNumber === null || $partNumber === '') {
                continue;
            }

            $byPart[(string) $partNumber][] = new ImportedPriceDTO(
                minQuantity: (int) $sheet->getCell('B'.$rowIndex)->getValue(),
                price: (float) $sheet->getCell('C'.$rowIndex)->getValue(),
                currency: (string) $sheet->getCell('D'.$rowIndex)->getValue(),
            );
        }

        return $byPart;
    }
}
