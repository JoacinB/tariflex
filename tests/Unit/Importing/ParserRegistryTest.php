<?php

declare(strict_types=1);

namespace Tests\Unit\Importing;

use App\Importing\Contracts\SupplierParser;
use App\Importing\Exceptions\ParserNotFoundException;
use App\Importing\ParserRegistry;
use Tests\TestCase;

final class ParserRegistryTest extends TestCase
{
    public function test_resolves_a_registered_code_to_the_configured_parser_instance(): void
    {
        config()->set('importing.parsers', [
            'fake' => FakeParser::class,
        ]);

        $registry = $this->app->make(ParserRegistry::class);

        $this->assertInstanceOf(FakeParser::class, $registry->resolve('fake'));
    }

    public function test_throws_parser_not_found_exception_for_unknown_supplier_code(): void
    {
        config()->set('importing.parsers', []);

        $registry = $this->app->make(ParserRegistry::class);

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('unknown');

        $registry->resolve('unknown');
    }
}

final class FakeParser implements SupplierParser
{
    public function parse(string $filePath): iterable
    {
        return [];
    }
}
