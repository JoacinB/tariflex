<?php

declare(strict_types=1);

namespace App\Importing\Parsers;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedProductDTO;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class AcmeParser implements SupplierParser
{
    public function parse(string $filePath): iterable
    {
        $sheet = IOFactory::load($filePath)->getActiveSheet();

        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = $row->getCellIterator();
            $cells->setIterateOnlyExistingCells(false);

            $values = [];
            foreach ($cells as $cell) {
                $values[] = $cell->getValue();
            }

            if ($values[0] === null || $values[0] === '') {
                continue;
            }

            yield new ImportedProductDTO(
                supplierReference: (string) $values[0],
                brand: (string) $values[1],
            );
        }
    }
}
