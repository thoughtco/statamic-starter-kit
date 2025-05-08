<?php

namespace App\Listeners;

use Statamic\Events;
use Thoughtco\StatamicCacheTracker\Jobs\InvalidateTags;

class EntryListener
{
    public function created(Events\EntryCreated $event)
    {
        // you may want to invalidate any pages containing a partial
        // eg latest_news, when a new entry is added, assuming the
        // item could be displayed. Think it out.
        //
        // switch ($event->entry->collection()?->handle())
        // {
        //     case 'news':
        //         InvalidateTags::dispatch(['partial:_panels/latest_news']);
        //     break;
        // }
    }

    public function deleted(Events\EntryDeleted $event)
    {
        // you may want to invalidate any pages containing a partial
        // eg latest_news, when an entry is deleted, assuming the
        // item was being displayed. Think it out.
        //
        // switch ($event->entry->collection()?->handle())
        // {
        //     case 'news':
        //         InvalidateTags::dispatch(['partial:_panels/latest_news']);
        //     break;
        // }
    }

    public function saved(Events\EntrySaved $event)
    {
        // pages will be automatically re-cached based on entry save/delete
        // but you may want to do something extra if the publish state of an
        // entry has changed, this could be triggered by the cron job marking
        // an item as published, or unpublished
        //
        // if ($event->entry->isDirty('published')) {
        //     InvalidateTags::dispatch([$event->entry->collection()->handle().':'.$event->entry->id()])
        //
        //     switch ($event->entry->collection()?->handle())
        //     {
        //         case 'news':
        //             InvalidateTags::dispatch(['partial:_panels/latest_news']);
        //         break;
        //     }
        // }
    }

    public function scheduleReached(Events\EntryScheduleReached $event)
    {
        $event->entry->save(); // we save the entry to kick the stache and run any invalidation
    }
}

?>
