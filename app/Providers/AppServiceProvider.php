<?php

namespace App\Providers;

use App\Caching\CacheInvalidator;
use Illuminate\Support\ServiceProvider;
use Statamic\Fieldtypes;
use Statamic\StaticCaching\Cacher;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CacheInvalidator::class, function ($app) {
            return new CacheInvalidator(
                $app[Cacher::class],
                $app['config']['statamic.static_caching.invalidation.rules']
            );
        });
    }

    public function boot()
    {
        Fieldtypes\Bard::setDefaultButtons([
            'h2',
            'h3',
            'bold',
            'italic',
            'unorderedlist',
            'orderedlist',
            'removeformat',
            'anchor',
        ]);
    }
}
