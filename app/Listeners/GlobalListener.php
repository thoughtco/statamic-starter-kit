<?php

namespace App\Listeners;

use Spatie\Geocoder\Facades\Geocoder;
use Statamic\Events;

class GlobalListener
{
    public function saved(Events\GlobalSetSaved $event)
    {
        switch ($event->globals->handle()) {
            case 'contact':
                $this->processContact($event->globals->inDefaultSite());
                break;
        }
    }

    // process anything to do with the contact global
    private function processContact($global)
    {
        // if we don't have latitude passed
        if (! $global->get('latitude')) {

            $location = Geocoder::getCoordinatesForAddress($global->get('address'));

            // if we have results
            if ($location['accuracy'] != 'result_not_found') {
                $global->set('latitude', $location['lat']);
                $global->set('longitude', $location['lng']);
                $global->save();
            }

        }
    }
}
