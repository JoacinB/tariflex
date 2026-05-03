<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['code' => 'acme', 'name' => 'Acme S.A.'],
            ['code' => 'globaltech', 'name' => 'GlobalTech Inc.'],
        ];

        foreach ($suppliers as $attributes) {
            Supplier::firstOrCreate(['code' => $attributes['code']], $attributes);
        }
    }
}
