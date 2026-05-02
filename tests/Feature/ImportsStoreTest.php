<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ImportsStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_a_minimal_fake_supplier_file_and_returns_summary(): void
    {
        $this->markTestIncomplete('Pending inner collaborators.');

        $file = new UploadedFile(
            base_path('tests/Fixtures/Excels/fake_minimal.xlsx'),
            'fake_minimal.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $response = $this->postJson('/api/imports', [
            'supplier_code' => 'fake',
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('created', 1);
        $response->assertJsonPath('updated', 0);
        $response->assertJsonPath('errors', []);
    }
}
