<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Http;
use Statamic\Events\UrlInvalidated;

class RecacheUrl
{
    /**
     * Handle when an entry is saved.
     */
    public function handle(UrlInvalidated $event)
    {
        Http::get($event->url);
    }
}
