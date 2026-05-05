<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController extends Controller
{
    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->with(['prices', 'taxes'])
                ->when(
                    $request->filled('brand'),
                    fn ($query) => $query->ofBrand((string) $request->string('brand'))
                )
                ->when(
                    $request->filled('reference'),
                    fn ($query) => $query->withReference((string) $request->string('reference'))
                )
                ->paginate($request->integer('per_page') ?: null)
        );
    }
}
