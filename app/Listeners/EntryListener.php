<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Entry;

class EntryListener
{
    /**
     * Handle when an entry is saved.
     */
    public function handle(EntrySaved $event)
    {
        switch ($event->entry->collection()->handle()){
                        
            case 'news':
                $this->clearPagesWithPanels(['news', 'article_select']); 
            break;

        }

        $this->clearCacheAndWarmUrl($event->entry);
    } 
    
    // clear any pages with the given handles
    private function clearEntriesWithPanels($panelHandles = [], $collectionHandle = 'pages', $fieldHandle = 'panels')
    {
        Entry::query()
            ->where('collection', $collectionHandle)
            ->get(['id', $fieldHandle])
            ->each(function($page) use ($fieldHandle, $panelHandles) {
                if ($this->hasPanel($page->get($fieldHandle, []), $panelHandles)) {
                    $this->clearCacheAndWarmUrl($page);
                }
            });        
    }

    // check whether an entry has any of the $panelHandles passed
    private function hasPanel($panels, $panelHandles = []) 
    {            
        return collect($panels)
            ->pluck('type')
            ->intersect($panelHandles)
            ->count() > 0;    
    }
    
    // clear cache and warm a URL again
    private function clearCacheAndWarmUrl($entry)
    {
        Cache::forget('entry_'.$entry->id());
        
        if ($uri = $entry->uri()) {
            Http::get(url($uri));
        }
    }
}

?>
