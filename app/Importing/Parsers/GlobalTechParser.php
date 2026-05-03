<?php

declare(strict_types=1);

namespace App\Importing\Parsers;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedProductDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class GlobalTechParser implements SupplierParser
{
    public function parse(string $filePath): iterable
    {
        $sheet = IOFactory::load($filePath)->getSheetByName('Products');

        foreach ($sheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $reference = $sheet->getCell('A'.$rowIndex)->getValue();

            if ($reference === null || $reference === '') {
                continue;
            }

            yield new ImportedProductDTO(
                supplierReference: (string) $reference,
                brand: (string) $sheet->getCell('B'.$rowIndex)->getValue(),
            );
        }
    }
}
