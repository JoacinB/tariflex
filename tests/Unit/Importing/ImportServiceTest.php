<?php

declare(strict_types=1);

namespace Tests\Unit\Importing;

use App\Importing\Contracts\SupplierParser;
use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
use App\Importing\ImportService;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_product_brand_prices_and_taxes_from_parsed_dto(): void
    {
        Supplier::factory()->create(['code' => 'fake', 'name' => 'Fake Co.']);

        $dto = new ImportedProductDTO(
            supplierReference: 'F-001',
            brand: 'FakeBrand',
            prices: [
                new ImportedPriceDTO(minQuantity: 1, price: 10.0, currency: 'EUR'),
            ],
            taxes: [
                new ImportedTaxDTO(
                    countryCode: 'ES',
                    type: 'percentage',
                    rate: 21.0,
                    amount: null,
                    currency: null,
                ),
            ],
        );

        $parser = new class($dto) implements SupplierParser
        {
            public function __construct(private ImportedProductDTO $dto) {}

            public function parse(string $filePath): iterable
            {
                yield $this->dto;
            }
        };

        $summary = (new ImportService($parser))->import('fake', '/dev/null');

        $this->assertSame(1, $summary->created);
        $this->assertSame(0, $summary->updated);
        $this->assertSame([], $summary->errors);

        $product = Product::where('supplier_reference', 'F-001')->firstOrFail();
        $this->assertSame('FakeBrand', $product->brand->name);
        $this->assertCount(1, $product->prices);
        $this->assertCount(1, $product->taxes);
    }

    public function test_re_importing_the_same_dto_updates_instead_of_duplicating(): void
    {
        Supplier::factory()->create(['code' => 'fake', 'name' => 'Fake Co.']);

        $dto = new ImportedProductDTO(
            supplierReference: 'F-001',
            brand: 'FakeBrand',
            prices: [new ImportedPriceDTO(minQuantity: 1, price: 10.0, currency: 'EUR')],
            taxes: [new ImportedTaxDTO(
                countryCode: 'ES',
                type: 'percentage',
                rate: 21.0,
                amount: null,
                currency: null,
            )],
        );

        $parser = new class($dto) implements SupplierParser
        {
            public function __construct(private ImportedProductDTO $dto) {}

            public function parse(string $filePath): iterable
            {
                yield $this->dto;
            }
        };

        $service = new ImportService($parser);
        $service->import('fake', '/dev/null');
        $second = $service->import('fake', '/dev/null');

        $this->assertSame(0, $second->created);
        $this->assertSame(1, $second->updated);
        $this->assertSame(1, Product::count());
        $this->assertSame(1, Product::firstOrFail()->prices()->count());
        $this->assertSame(1, Product::firstOrFail()->taxes()->count());
    }
}
