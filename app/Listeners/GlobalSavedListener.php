<?php

namespace App\Listeners;

use Spatie\Geocoder\Facades\Geocoder;
use Statamic\Events\GlobalSetSaved;

class GlobalSavedListener
{
    /**
     * Handle when an entry is saved.
     */
    public function handle(GlobalSetSaved $event)
    {
        switch ($event->globals->handle()){

            case 'contact':
                $this->processContact($event->globals->inDefaultSite());
            break;

        }
    }

    // process anything to do with the contact global
    private function processContact($global)
    {

        // if we don't have latitude passed
        if (isset($global->data()['latitude']) === false){

            $location = Geocoder::getCoordinatesForAddress($global->data()['address']);

		    // if we have results
		    if ($location['accuracy'] != 'result_not_found'){

                $global->set('latitude', $location['lat']);
                $global->set('longitude', $location['lng']);
                $global->save();

            }

        }

    }

}

?>
