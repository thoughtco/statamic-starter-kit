<?php

return [
    'file_types' => ['css', 'js'],
    'ignore_urls' => [
        '/^\/(plausible)\/.*(js)/', 
        '/\/(livewire)\/.*(js)/', 
    ],
    'minify_enabled' => function(){
        return isset($_GET['no_min']) == false;
    },
    'min_path' => '/assets/min/', // relative to public
];
