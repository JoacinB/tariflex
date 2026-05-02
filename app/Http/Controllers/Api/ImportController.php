<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportRequest;
use App\Importing\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function store(StoreImportRequest $request, ImportService $service): JsonResponse
    {
        $summary = $service->import(
            $request->string('supplier_code')->toString(),
            $request->file('file')->getRealPath(),
        );

        return response()->json($summary);
    }
}
