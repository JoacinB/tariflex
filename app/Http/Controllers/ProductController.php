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
        $query = Product::query();

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('name', $request->string('brand')));
        }

        return JsonResource::collection($query->paginate());
    }
}
