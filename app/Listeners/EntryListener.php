<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
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

                $this->clearPagesWithPanels(['news']);
                $this->clearPagesWithPanels(['article_select']); 
                
            break;

        }

        Cache::forget('entry_'.$event->entry->id());
    } 
    
    // clear any pages with the given handles
    private function clearPagesWithPanels($panelHandles = [], $collectionHandle = 'pages', $fieldHandle = 'panels')
    {
        Entry::query()
            ->where('collection', $collectionHandle)
            ->get(['id', $fieldHandle])
            ->each(function($page) use ($fieldHandle, $panelHandles) {
                if ($this->hasPanel($page->get($fieldHandle, []), $panelHandles)) {
                    Cache::forget('entry_'.$page->id());
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
}

?>