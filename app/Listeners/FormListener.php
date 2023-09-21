<?php

namespace App\Listeners;

// use \DrewM\MailChimp\MailChimp;

use Statamic\Events\FormSubmitted;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
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

        switch ($event->submission->form()->handle())
        {

            default:

                $this->processSomething();

            break;

        }

    }

    // process function
    public function processSomething(){

    }

}

?>
