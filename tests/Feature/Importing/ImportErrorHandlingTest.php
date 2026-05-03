<?php

declare(strict_types=1);

namespace Tests\Feature\Importing;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ImportErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_xlsx_returns_summary_with_friendly_error(): void
    {
        Supplier::factory()->create(['code' => 'acme', 'name' => 'Acme S.A.']);

        $real = file_get_contents(base_path('tests/Fixtures/Excels/acme.xlsx'));
        $tmp = tempnam(sys_get_temp_dir(), 'malformed_').'.xlsx';
        file_put_contents($tmp, substr($real, 0, (int) (strlen($real) / 2)));

        $file = new UploadedFile(
            $tmp,
            'broken.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $response = $this->postJson('/api/imports', [
            'supplier_code' => 'acme',
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('created', 0);
        $response->assertJsonPath('updated', 0);
        $response->assertJsonPath('errors.0.row', null);
        $this->assertStringContainsString(
            'could not be read',
            $response->json('errors.0.error'),
        );
    }
}
