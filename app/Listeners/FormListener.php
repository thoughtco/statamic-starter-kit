<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\User;
use Statamic\Support\Str;

/*

    Setup a function for each form that needs handled

*/

class FormListener
{
    /**
     * Handle the form submitted event.
     */
    public function handle(FormSubmitted $event)
    {

        switch ($event->submission->form()->handle()) {

            case 'contact':

                $this->processContactForm($event);

            break;

        }

    }

    // process function
    public function processContactForm($event)
    {

    }

}
