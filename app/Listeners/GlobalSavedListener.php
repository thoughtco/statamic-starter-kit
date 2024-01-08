<?php

namespace App\Listeners;

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

            // get google api key
            $googleApiKey = env('GOOGLE_API', '');

		    // get lat/long on basis of postcode
		    $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.urlencode($global->data()['address']).'&sensor=false&key='.$googleApiKey);

            // decode google return
		    $output = json_decode($geocode);

		    // if we have results
		    if (isset($output->results) && sizeof($output->results) > 0){

                $global->set('latitude', $output->results[0]->geometry->location->lat);
                $global->set('longitude', $output->results[0]->geometry->location->lng);
                $global->save();

            }

        }

    }

}

?>
