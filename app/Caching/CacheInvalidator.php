<?php

namespace App\Caching;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\StaticCaching\DefaultInvalidator;

class CacheInvalidator extends DefaultInvalidator
{
    public function invalidate($item)
    {
        if ($this->rules === 'all') {
            return $this->cacher->flush();
        }

        $urls = false;

        // all the logic from your example here...
        if ($item instanceof Entry) {
            $urls = $this->clearCacheForEntry($item);
        } else if ($item instanceof GlobalSet) {
            $urls = $this->clearCacheForGlobal($item);
        } else if ($item instanceof Nav) {
            $urls = $this->clearCacheForNav($item);
        } else if ($item instanceof Term) {
            $urls = $this->clearCacheForNav($item);
        }

        if ($urls) {
            $this->cacher->invalidateUrls($urls);
        }
    }

    private function clearCacheForEntry($entry)
    {
        switch ($entry->collection())
        {
            case 'news':
                return $this->clearEntriesWithPanels(['news', 'article_select']);
            break;
        }
    }

    private function clearCacheForGlobal($global)
    {
        switch ($global->handle())
        {
            case 'settings':
                // either return a set of urls
                // or flush all (when its something that appears on all pages)
                $this->cacher->flush();

                return false;
            break;
        }

        return false;
    }

    private function clearCacheForNav($nav)
    {
        // navs should always be cached as they are painfully slow
        Cache::forget('nav::'.$nav->handle());

        switch ($nav->handle())
        {
            case 'settings':
                // either return a set of urls
                // or flush all (when its a nav that appears on all pages)
                $this->cacher->flush();

                return false;
            break;
        }

        return false;
    }

    private function clearCacheForTerm($term)
    {
        switch ($term->taxonomyHandle())
        {
            //
        }

        return false;
    }

    // clear any pages with the given handles
    private function clearEntriesWithPanels($panelHandles = [], $collectionHandle = 'pages', $fieldHandle = 'panels')
    {
        return Entry::query()
            ->where('collection', $collectionHandle)
            ->filter(function($page) use ($fieldHandle, $panelHandles) {
                return this->hasPanel($page->get($fieldHandle, []), $panelHandles);
            })
            ->map()
            ->url
            ->all();
    }

    // check whether an entry has any of the $panelHandles passed
    private function hasPanel($panels, $panelHandles = [])
    {
        return collect($panels)
            ->pluck('type')
            ->intersect($panelHandles)
            ->count() > 0;
    }
}
