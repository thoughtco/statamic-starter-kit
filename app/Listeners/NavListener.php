<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;

class NavListener
{
    public function handle($event)
    {
        // what nav are we wanting to clear?
        switch ($event->tree->handle()) {
            
            case 'main';
                Cache::forget('main_nav');
            break;    
            
        }
        
    }
}