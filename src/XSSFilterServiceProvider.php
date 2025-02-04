<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Support\ServiceProvider;
use MasterRO\LaravelXSSFilter\Cleaner\Cleaner;
use MasterRO\LaravelXSSFilter\Cleaner\CleanerConfig;

class XSSFilterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/xss-filter.php' => config_path('xss-filter.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xss-filter.php', 'xss-filter');

        $this->app->scoped(Cleaner::class, static function () {
            return new Cleaner(CleanerConfig::fromArray(config('xss-filter')));
        });
    }
}
