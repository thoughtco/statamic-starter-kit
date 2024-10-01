<?php

namespace App\Providers;

use App\Caching\StaticWarmExtras;
use Illuminate\Support\ServiceProvider;
use Statamic\Console\Commands\StaticWarm;
use Statamic\Fieldtypes;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        StaticWarm::hook('additional', function ($urls, $next) {
            return $next($urls->merge((new StaticWarmExtras)->handle()));
        });

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
