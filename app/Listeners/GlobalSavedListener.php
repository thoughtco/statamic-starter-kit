<?php

namespace App\Listeners;

use App\Classes\LocationHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Statamic\Events\GlobalSetSaved;
use Statamic\Facades\GlobalSet;

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
            $googleApiKey = GlobalSet::findByHandle('contact')->in('default')->get('api_key');
                                                                                                         
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