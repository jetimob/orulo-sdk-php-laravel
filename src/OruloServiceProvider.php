<?php

namespace Jetimob\Orulo;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redis;
use Jetimob\Orulo\Console\ClearCache;
use Jetimob\Orulo\Console\InstallOruloPackage;

class OruloServiceProvider extends ServiceProvider
{
    private function getConfigPath(): string
    {
        return sprintf('%s/../config/config.php', __DIR__);
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'orulo');

        $this->app->bind('orulo', function ($app) {
            return new Orulo($app->config->get('orulo'));
        });

        $this->app->singleton('Orulo\Cache', function ($app) {
            $config = $app->config->get('orulo');
            return Redis::connection(Arr::get($config, 'redis_connection', null));
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigPath() => config_path('orulo.php')
            ], 'config');

            $this->commands([
                InstallOruloPackage::class,
                ClearCache::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['orulo', 'Orulo\\Cache'];
    }
}
