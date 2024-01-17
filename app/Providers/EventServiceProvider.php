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
        Events\FormSubmitted::class => [
            Listeners\FormListener::class,
        ],
        Events\GlobalSetSaved::class => [
            Listeners\GlobalSavedListener::class,
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
