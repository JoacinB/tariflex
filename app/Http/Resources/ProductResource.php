<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_reference' => $this->supplier_reference,
            'prices' => $this->prices->map(fn ($price) => [
                'min_quantity' => $price->min_quantity,
                'price' => (float) $price->price,
                'currency' => $price->currency,
            ])->all(),
        ];
    }
}
