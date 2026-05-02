<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductTax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductTax>
 */
class ProductTaxFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'country_code' => 'ES',
            'unit' => null,
            'type' => 'percentage',
            'rate' => 21.00,
            'amount' => null,
            'currency' => null,
        ];
    }
}
