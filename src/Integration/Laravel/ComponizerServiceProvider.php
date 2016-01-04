<?php

namespace Gavrya\Componizer\Integration\Laravel;

use Gavrya\Componizer\Componizer;
use Gavrya\Componizer\ComponizerConfig;
use Illuminate\Support\ServiceProvider;

class ComponizerServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('componizer', function ($app) {
            $config = [
                ComponizerConfig::CONFIG_LANG => config('app.locale', 'en'),
                ComponizerConfig::CONFIG_CACHE_DIR => storage_path(),
                ComponizerConfig::CONFIG_PUBLIC_DIR => public_path(),
                ComponizerConfig::CONFIG_PREVIEW_URL => '/componizer/preview.php',
            ];

            $componizerConfig = new ComponizerConfig($config);

            return new Componizer($componizerConfig);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        return ['componizer'];
    }
}
