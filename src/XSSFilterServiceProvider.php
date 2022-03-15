<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Support\ServiceProvider;

class XSSFilterServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/xss-filter.php' => config_path('xss-filter.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/xss-filter.php', 'xss-filter');

        $this->app->singleton(Cleaner::class, static function () {
            return new Cleaner(CleanerConfig::fromArray(config('xss-filter')));
        });
    }
}
