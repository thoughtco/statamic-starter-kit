<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Statamic\Events\NavTreeSaved;

class NavTreeListener
{
    public function handle(NavTreeSaved $event)
    {
        // what nav are we wanting to clear?
        switch ($event->tree->handle()) {
            
            case 'main';
                Cache::forget('main_nav');
            break;    
            
        }
        
    }
}