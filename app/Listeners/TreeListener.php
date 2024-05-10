<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Statamic\Events;
use Thoughtco\StatamicCacheTracker\Facades\Tracker;

class TreeListener
{
    public function deleted(Events\CollectionTreeDeleted|Events\NavTreeDeleted $event)
    {
        // on delete we want to forget any cache keys associated with this nav
        // and we want to invalidate any pages using the nav
        //
        // Cache::forget('nav::'.$nav->handle());
        // Tracker::invalidate(['partials:_partials/layout/nav']);

    }

    public function saved(Events\CollectionTreeSaved|Events\NavTreeSaved $event)
    {
        // on save we want to forget any cache keys associated with this nav
        //
        // Cache::forget('nav::'.$nav->handle());
        // Tracker::invalidate(['partials:_partials/layout/nav']);
    }
}
