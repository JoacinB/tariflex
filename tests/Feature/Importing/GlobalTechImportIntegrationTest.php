<?php

declare(strict_types=1);

namespace Tests\Feature\Importing;

use App\Importing\ImportService;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalTechImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_globaltech_fixture_imports_into_db_via_registered_parser(): void
    {
        Supplier::factory()->create(['code' => 'globaltech', 'name' => 'GlobalTech Inc.']);

        $summary = $this->app->make(ImportService::class)->import(
            'globaltech',
            __DIR__.'/../../Fixtures/Excels/globaltech.xlsx',
        );

        $this->assertSame(3, $summary->created);
        $this->assertSame(0, $summary->updated);
        $this->assertSame([], $summary->errors);

        $first = Product::where('supplier_reference', 'GT-100')->firstOrFail();
        $this->assertSame('TechCo', $first->brand->name);
        $this->assertCount(2, $first->prices);
        $this->assertCount(2, $first->taxes);

        $mixedTax = Product::where('supplier_reference', 'GT-300')->firstOrFail();
        $this->assertCount(2, $mixedTax->prices);
        $fixed = $mixedTax->taxes->firstWhere('type', 'fixed');
        $this->assertNotNull($fixed);
        $this->assertSame('US', $fixed->country_code);
        $this->assertEqualsWithDelta(5.00, (float) $fixed->amount, 0.0001);
        $this->assertSame('USD', $fixed->currency);
    }
}
