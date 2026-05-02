<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_code' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $parsers = (array) config('importing.parsers', []);
                    if (! array_key_exists($value, $parsers)) {
                        $fail("No parser registered for supplier code [{$value}].");
                    }
                },
            ],
            'file' => ['required', 'file', 'mimes:xlsx'],
        ];
    }
}
