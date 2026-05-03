<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ImportsStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_supplier_code_returns_422_with_field_error(): void
    {
        config()->set('importing.parsers', []);

        $file = new UploadedFile(
            base_path('tests/Fixtures/Excels/fake_minimal.xlsx'),
            'fake_minimal.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $response = $this->postJson('/api/imports', [
            'supplier_code' => 'nope',
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['supplier_code']);
    }

    public function test_missing_file_returns_422_with_field_error(): void
    {
        config()->set('importing.parsers', ['fake' => 'X']);

        $response = $this->postJson('/api/imports', [
            'supplier_code' => 'fake',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_non_xlsx_file_returns_422_with_field_error(): void
    {
        config()->set('importing.parsers', ['fake' => 'X']);

        $file = UploadedFile::fake()->create('not-an-excel.csv', 1, 'text/csv');

        $response = $this->postJson('/api/imports', [
            'supplier_code' => 'fake',
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }
}
