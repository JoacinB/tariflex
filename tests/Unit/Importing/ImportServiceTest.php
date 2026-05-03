<?php

declare(strict_types=1);

namespace Tests\Unit\Importing;

use App\Importing\DTOs\ImportedPriceDTO;
use App\Importing\DTOs\ImportedProductDTO;
use App\Importing\DTOs\ImportedTaxDTO;
use App\Importing\ImportService;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\FakeSupplierParser;
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

        $summary = $this->serviceWithDtos([$dto])->import('fake', '/dev/null');

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

        $service = $this->serviceWithDtos([$dto]);
        $service->import('fake', '/dev/null');
        $second = $service->import('fake', '/dev/null');

        $this->assertSame(0, $second->created);
        $this->assertSame(1, $second->updated);
        $this->assertSame(1, Product::count());
        $this->assertSame(1, Product::firstOrFail()->prices()->count());
        $this->assertSame(1, Product::firstOrFail()->taxes()->count());
    }

    public function test_failure_on_one_dto_isolates_to_that_row_and_collects_error(): void
    {
        Supplier::factory()->create(['code' => 'fake', 'name' => 'Fake Co.']);

        Brand::saving(function (Brand $brand): void {
            if ($brand->name === 'BROKEN') {
                throw new RuntimeException('persistence boom');
            }
        });

        $dtos = [
            new ImportedProductDTO(
                supplierReference: 'F-001',
                brand: 'Alpha',
                prices: [new ImportedPriceDTO(minQuantity: 1, price: 10.0, currency: 'EUR')],
            ),
            new ImportedProductDTO(
                supplierReference: 'F-002',
                brand: 'BROKEN',
                prices: [new ImportedPriceDTO(minQuantity: 1, price: 20.0, currency: 'EUR')],
            ),
            new ImportedProductDTO(
                supplierReference: 'F-003',
                brand: 'Gamma',
                prices: [new ImportedPriceDTO(minQuantity: 1, price: 30.0, currency: 'EUR')],
            ),
        ];

        $summary = $this->serviceWithDtos($dtos)->import('fake', '/dev/null');

        $this->assertSame(2, $summary->created);
        $this->assertSame(0, $summary->updated);
        $this->assertCount(1, $summary->errors);
        $this->assertSame(2, $summary->errors[0]['row']);
        $this->assertStringContainsString('persistence boom', $summary->errors[0]['error']);

        $this->assertTrue(Product::where('supplier_reference', 'F-001')->exists());
        $this->assertFalse(Product::where('supplier_reference', 'F-002')->exists());
        $this->assertTrue(Product::where('supplier_reference', 'F-003')->exists());
        $this->assertFalse(Brand::where('name', 'BROKEN')->exists());
    }

    /**
     * @param  list<ImportedProductDTO>  $dtos
     */
    private function serviceWithDtos(array $dtos): ImportService
    {
        $this->app->instance(FakeSupplierParser::class, new FakeSupplierParser($dtos));
        config()->set('importing.parsers', ['fake' => FakeSupplierParser::class]);

        return $this->app->make(ImportService::class);
    }
}
