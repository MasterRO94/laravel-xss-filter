<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter\Facade;

use Illuminate\Support\Facades\Facade;
use MasterRO\LaravelXSSFilter\Cleaner\Cleaner;

class XSSCleaner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Cleaner::class;
    }
}
