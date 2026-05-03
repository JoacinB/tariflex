<?php

declare(strict_types=1);

namespace App\Importing\Parsers;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class GlobalTechParser implements SupplierParser
{
    public function parse(string $filePath): iterable
    {
        $spreadsheet = IOFactory::load($filePath);
        $pricesByPart = $this->pricesByPart($spreadsheet);
        $taxesByPart = $this->taxesByPart($spreadsheet);

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
                taxes: $taxesByPart[$reference] ?? [],
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

    /**
     * @return array<string, list<ImportedTaxDTO>>
     */
    private function taxesByPart(Spreadsheet $spreadsheet): array
    {
        $sheet = $spreadsheet->getSheetByName('Taxes');
        $byPart = [];

        foreach ($sheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $partNumber = $sheet->getCell('A'.$rowIndex)->getValue();

            if ($partNumber === null || $partNumber === '') {
                continue;
            }

            $taxType = (string) $sheet->getCell('D'.$rowIndex)->getValue();
            $rate = $sheet->getCell('E'.$rowIndex)->getValue();
            $amount = $sheet->getCell('F'.$rowIndex)->getValue();
            $currency = $sheet->getCell('G'.$rowIndex)->getValue();

            $byPart[(string) $partNumber][] = new ImportedTaxDTO(
                countryCode: (string) $sheet->getCell('B'.$rowIndex)->getValue(),
                type: $taxType === 'Amount' ? 'fixed' : 'percentage',
                rate: $rate === null || $rate === '' ? null : (float) $rate,
                amount: $amount === null || $amount === '' ? null : (float) $amount,
                currency: $currency === null || $currency === '' ? null : (string) $currency,
            );
        }

        return $byPart;
    }
}
