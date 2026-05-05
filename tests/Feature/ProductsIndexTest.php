<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductTax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_paginated_data_when_no_products_exist(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonPath('data', []);
    }

    public function test_filters_products_by_brand_name(): void
    {
        $acme = Brand::factory()->create(['name' => 'Acme']);
        $globex = Brand::factory()->create(['name' => 'Globex']);

        Product::factory()->for($acme)->count(2)->create();
        Product::factory()->for($globex)->count(3)->create();

        $response = $this->getJson('/api/products?brand=Acme');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_response_includes_pagination_metadata(): void
    {
        Product::factory()->count(7)->create();

        $response = $this->getJson('/api/products?per_page=5&page=2');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonPath('meta.total', 7);
    }

    public function test_rejects_per_page_above_max(): void
    {
        $response = $this->getJson('/api/products?per_page=9999999');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_response_includes_price_tiers_ordered_by_min_quantity(): void
    {
        $product = Product::factory()->create();
        ProductPrice::factory()->for($product)->create([
            'min_quantity' => 10,
            'price' => 8.50,
            'currency' => 'EUR',
        ]);
        ProductPrice::factory()->for($product)->create([
            'min_quantity' => 1,
            'price' => 10.00,
            'currency' => 'EUR',
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonCount(2, 'data.0.prices');
        $response->assertJsonPath('data.0.prices.0.min_quantity', 1);
        $response->assertJsonPath('data.0.prices.1.min_quantity', 10);
    }

    public function test_response_includes_taxes_with_type_rate_and_amount(): void
    {
        $product = Product::factory()->create();
        ProductTax::factory()->for($product)->create([
            'country_code' => 'ES',
            'type' => 'percentage',
            'rate' => 21.00,
            'amount' => null,
            'currency' => null,
        ]);
        ProductTax::factory()->for($product)->create([
            'country_code' => 'FR',
            'type' => 'fixed',
            'rate' => null,
            'amount' => 5.50,
            'currency' => 'EUR',
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonCount(2, 'data.0.taxes');
        $response->assertJsonFragment([
            'country_code' => 'ES',
            'type' => 'percentage',
            'rate' => 21.0,
            'amount' => null,
        ]);
        $response->assertJsonFragment([
            'country_code' => 'FR',
            'type' => 'fixed',
            'rate' => null,
            'amount' => 5.5,
        ]);
    }

    public function test_filters_products_by_brand_and_reference(): void
    {
        $acme = Brand::factory()->create(['name' => 'Acme']);

        $target = Product::factory()->for($acme)->create(['supplier_reference' => 'A-001']);
        Product::factory()->for($acme)->create(['supplier_reference' => 'A-002']);

        $response = $this->getJson('/api/products?brand=Acme&reference=A-001');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $target->id);
    }
}
