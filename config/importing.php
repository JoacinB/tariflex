<?php

declare(strict_types=1);
use App\Importing\Parsers\AcmeParser;

return [

    /*
    |--------------------------------------------------------------------------
    | Supplier parsers
    |--------------------------------------------------------------------------
    |
    | Map each supplier `code` to the FQCN of the parser that knows how to
    | read its tariff Excel file. Adding a new supplier = add one entry here
    | plus a class implementing App\Importing\Contracts\SupplierParser.
    |
    */

    'parsers' => [
        'acme' => AcmeParser::class,
        // 'globaltech' => \App\Importing\Parsers\GlobalTechParser::class,
    ],

];
