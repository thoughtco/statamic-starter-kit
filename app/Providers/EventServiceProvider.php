<?php

namespace App\Providers;

use App\Listeners;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Statamic\Events;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        Events\CollectionTreeDeleted::class => [
            [Listeners\TreeListener::class, 'deleted'],
        ],

        Events\CollectionTreeSaved::class => [
            [Listeners\TreeListener::class, 'saved'],
        ],

        Events\EntryCreated::class => [
            [Listeners\EntryListener::class, 'created'],
        ],

        Events\EntryDeleted::class => [
            [Listeners\EntryListener::class, 'deleted'],
        ],

        Events\EntrySaved::class => [
            [Listeners\EntryListener::class, 'saved'],
        ],

        Events\EntryScheduleReached::class => [
            [Listeners\EntryListener::class, 'scheduleReached'],
        ],

        Events\FormSubmitted::class => [
            Listeners\FormListener::class,
        ],

        Events\GlobalSetSaved::class => [
            [Listeners\GlobalListener::class, 'saved'],
        ],

        Events\NavTreeDeleted::class => [
            [Listeners\TreeListener::class, 'deleted'],
        ],

        Events\NavTreeSaved::class => [
            [Listeners\TreeListener::class, 'saved'],
        ],

        Events\UrlInvalidated::class => [
            Listeners\RecacheUrl::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
