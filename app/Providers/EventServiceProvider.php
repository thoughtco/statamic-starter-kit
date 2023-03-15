<?php

namespace App\Providers;

use App\Listeners;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Statamic\Events;
use Statamic\Events\EntrySaved;
use Statamic\Events\FormSubmitted;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\NavTreeSaved;

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
        EntrySaved::class => [
            \App\Listeners\EntryListener::class,
        ],                       
        FormSubmitted::class => [
            \App\Listeners\FormListener::class,
        ],
        GlobalSetSaved::class => [
            \App\Listeners\GlobalSavedListener::class,
        ],
        NavTreeSaved::class => [
            \App\Listeners\NavTreeListener::class,
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
