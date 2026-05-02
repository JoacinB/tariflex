<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
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
