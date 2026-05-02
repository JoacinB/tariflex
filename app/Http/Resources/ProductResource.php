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
            'taxes' => $this->taxes->map(fn ($tax) => [
                'country_code' => $tax->country_code,
                'unit' => $tax->unit,
                'type' => $tax->type,
                'rate' => $tax->rate !== null ? (float) $tax->rate : null,
                'amount' => $tax->amount !== null ? (float) $tax->amount : null,
                'currency' => $tax->currency,
            ])->all(),
        ];
    }
}
