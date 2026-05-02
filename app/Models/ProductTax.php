<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductTaxFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTax extends Model
{
    /** @use HasFactory<ProductTaxFactory> */
    use HasFactory;

    protected $casts = [
        'rate' => 'decimal:4',
        'amount' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
