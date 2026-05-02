<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/imports', [ImportController::class, 'store']);
