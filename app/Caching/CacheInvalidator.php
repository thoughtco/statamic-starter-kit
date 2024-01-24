<?php

namespace App\Caching;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Structures\NavTree;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades;
use Statamic\Statamic;
use Statamic\StaticCaching\DefaultInvalidator;

class CacheInvalidator extends DefaultInvalidator
{
    public function invalidate($item)
    {
        if ($this->rules === 'all') {
            return $this->cacher->flush();
        }

        $urls = false;

        if ($item instanceof Entry) {
            $urls = $this->clearCacheForEntry($item);

            if ($url = $item->url()) {
                $urls->push($url);
            }
        } else if ($item instanceof GlobalSet) {
            $urls = $this->clearCacheForGlobal($item);
        } else if ($item instanceof Nav) {
            $urls = $this->clearCacheForNav($item);
        } else if ($item instanceof CollectionTree) {
            $urls = $this->clearCacheForCollectionTree($item);
        } else if ($item instanceof NavTree) {
            $urls = $this->clearCacheForNavTree($item);
        } else if ($item instanceof Term) {
            $urls = $this->clearCacheForTerm($item);
        }

        if ($urls instanceof Collection) {
            $this->cacher->invalidateUrls($urls->all());

            // sometimes if you clear artisan cache, the static cache is out of sync
            // this gets around that
            $urls->each(function ($url) {
                $file = $this->cacher->getFilePath($url);
                if (file_exists($file)) {
                    unlink($file);
                }
            });
        }
    }

    private function clearCacheForEntry($entry): ?Collection
    {
        switch ($entry->collection())
        {
            case 'news':
                return $this->clearEntriesWithPanels(['news_listing', 'featured_news']);
            break;

            case 'artists':
                return $this->clearEntriesWithPanels(['artists_listing', 'air_slider', 'discipline_listing', 'previous_air'])
                    ->merge(collect($entry->sets)->map(function ($set) use ($entry) {
                        return $entry->url().'/'.Statamic::modify($set->title)->modify('slugify');
                    }));
            break;
        }

        return collect();
    }

    private function clearCacheForGlobal($global): ?Collection
    {
        switch ($global->handle())
        {
            case 'settings':
                // either return a set of urls
                // or flush all (when its something that appears on all pages)
                $this->cacher->flush();

                return null;
            break;
        }

        return null;
    }

    private function clearCacheForNav($nav): ?Collection
    {
        return null;
    }

    private function clearCacheForCollectionTree($nav): ?Collection
    {
        switch ($nav->handle())
        {
            case 'artists':

                // cache keys
                Cache::forget('nav::main');
                Cache::forget('nav::side');

                // either return a set of urls
                // or flush all (when its a nav that appears on all pages)
                $this->cacher->flush();

                return null;
            break;
        }

        return null;
    }

    private function clearCacheForNavTree($nav): ?Collection
    {
        // navs should always be cached as they are painfully slow
        Cache::forget('nav::'.$nav->handle());

        // cache keys
        Cache::forget('nav::main');
        Cache::forget('nav::side');

        switch ($nav->handle())
        {
            case 'main':
                // either return a set of urls
                // or flush all (when its a nav that appears on all pages)
                $this->cacher->flush();

                return null;
            break;
        }

        return null;
    }

    private function clearCacheForTerm($term): ?Collection
    {
        switch ($term->taxonomyHandle())
        {
            //
        }

        return null;
    }

    // clear any pages with the given handles
    private function clearEntriesWithPanels($panelHandles = [], $collectionHandle = 'pages', $fieldHandle = 'panels'): Collection
    {
        return Facades\Entry::query()
            ->where('collection', $collectionHandle)
            ->get()
            ->filter(function($page) use ($fieldHandle, $panelHandles) {
                return $this->hasPanel($page->get($fieldHandle, []), $panelHandles);
            })
            ->map
            ->url();
    }

    // check whether an entry has any of the $panelHandles passed
    private function hasPanel($panels, $panelHandles = []): bool
    {
        return collect($panels)
            ->pluck('type')
            ->intersect($panelHandles)
            ->count() > 0;
    }
}
