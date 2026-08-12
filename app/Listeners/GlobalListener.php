<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Spatie\Geocoder\Facades\Geocoder;
use Statamic\Events;
use Statamic\Facades\CP\Toast;

class GlobalListener
{
    public function saved(Events\GlobalSetSaved $event)
    {
        switch ($event->globals->handle())
        {
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

            try {
                $location = Geocoder::getCoordinatesForAddress($global->get('address'));
            } catch (\Throwable $e) {
                Log::warning('Failed to geocode contact address: '.$e->getMessage());
                Toast::error('Could not look up co-ordinates for the address — check the address and the geocoding API key.');
                return;
            }

            // if we have results
            if ($location['accuracy'] != 'result_not_found'){
                $global->merge([
                  'latitude' => $location['lat'],
                  'longitude' => $location['lng'],
                ]);
                  
                $global->save();
            }

        }
    }
}

?>
