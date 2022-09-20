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
        FormSubmitted::class => [
            \App\Listeners\FormListener::class,
        ],
        EntrySaved::class => [
            \App\Listeners\EntryListener::class,
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
