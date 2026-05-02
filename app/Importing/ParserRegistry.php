<?php

declare(strict_types=1);

namespace App\Importing;

use App\Importing\Contracts\SupplierParser;
use App\Importing\Exceptions\ParserNotFoundException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;

final class ParserRegistry
{
    public function __construct(
        private Config $config,
        private Container $container,
    ) {}

    public function resolve(string $supplierCode): SupplierParser
    {
        $parsers = $this->config->get('importing.parsers', []);

        if (! isset($parsers[$supplierCode])) {
            throw new ParserNotFoundException($supplierCode);
        }

        return $this->container->make($parsers[$supplierCode]);
    }
}
