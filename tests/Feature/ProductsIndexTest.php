<?php

declare(strict_types=1);

namespace Tests\Feature;

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
}
