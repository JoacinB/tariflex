<?php

declare(strict_types=1);

namespace Tests\Feature\Importing;

use App\Importing\ImportService;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AcmeImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_acme_fixture_imports_into_db_via_registered_parser(): void
    {
        Supplier::factory()->create(['code' => 'acme', 'name' => 'Acme S.A.']);

        $summary = $this->app->make(ImportService::class)->import(
            'acme',
            __DIR__.'/../../Fixtures/Excels/acme.xlsx',
        );

        $this->assertSame(3, $summary->created);
        $this->assertSame(0, $summary->updated);
        $this->assertSame([], $summary->errors);

        $first = Product::where('supplier_reference', 'A-001')->firstOrFail();
        $this->assertSame('Acme', $first->brand->name);
        $this->assertCount(3, $first->prices);
        $this->assertCount(2, $first->taxes);

        $minimal = Product::where('supplier_reference', 'A-002')->firstOrFail();
        $this->assertCount(1, $minimal->prices);
        $this->assertCount(1, $minimal->taxes);
    }
}
