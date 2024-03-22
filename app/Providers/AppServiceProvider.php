<?php

namespace App\Providers;

use App\Caching\StaticWarmExtras;
use Illuminate\Support\ServiceProvider;
use Statamic\Console\Commands\StaticWarm;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        StaticWarm::hook('additional', function ($urls, $next) {
            return $next($urls->merge(StaticWarmExtras::handle()));
        });
    }
}
