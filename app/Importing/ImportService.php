<?php

declare(strict_types=1);

namespace App\Importing;

use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportSummary;
use App\Importing\Exceptions\ParseException;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ImportService
{
    public function __construct(private ParserRegistry $registry) {}

    public function import(string $supplierCode, string $filePath): ImportSummary
    {
        $supplier = Supplier::where('code', $supplierCode)->firstOrFail();
        $parser = $this->registry->resolve($supplierCode);
        $summary = new ImportSummary;

        $row = 0;
        try {
            foreach ($parser->parse($filePath) as $dto) {
                $row++;

                try {
                    DB::transaction(fn () => $this->persist($supplier, $dto, $summary));
                } catch (Throwable $e) {
                    $summary->errors[] = ['row' => $row, 'error' => $e->getMessage()];
                }
            }
        } catch (ParseException $e) {
            $summary->errors[] = ['row' => $e->row, 'error' => $e->getMessage()];
        }

        return $summary;
    }

    private function persist(Supplier $supplier, ImportedProductDTO $dto, ImportSummary $summary): void
    {
        $brand = Brand::firstOrCreate(['name' => $dto->brand]);

        $existing = Product::where('supplier_id', $supplier->id)
            ->where('supplier_reference', $dto->supplierReference)
            ->first();

        $product = $existing ?? new Product;
        $product->brand_id = $brand->id;
        $product->supplier_id = $supplier->id;
        $product->supplier_reference = $dto->supplierReference;
        $product->save();

        $product->prices()->delete();
        foreach ($dto->prices as $price) {
            $product->prices()->create([
                'min_quantity' => $price->minQuantity,
                'price' => $price->price,
                'currency' => $price->currency,
            ]);
        }

        $product->taxes()->delete();
        foreach ($dto->taxes as $tax) {
            $product->taxes()->create([
                'country_code' => $tax->countryCode,
                'type' => $tax->type,
                'rate' => $tax->rate,
                'amount' => $tax->amount,
                'currency' => $tax->currency,
            ]);
        }

        if ($existing === null) {
            $summary->created++;
        } else {
            $summary->updated++;
        }
    }
}
