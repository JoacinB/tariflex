<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return JsonResource::collection(
            Product::query()
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
