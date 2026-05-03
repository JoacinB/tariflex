<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)->orderBy('min_quantity');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(ProductTax::class);
    }

    public function scopeOfBrand(Builder $query, string $name): void
    {
        $query->whereHas('brand', fn (Builder $q) => $q->where('name', $name));
    }

    public function scopeWithReference(Builder $query, string $reference): void
    {
        $query->where('supplier_reference', $reference);
    }
}
