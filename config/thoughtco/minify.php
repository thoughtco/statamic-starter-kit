<?php

return [
    'file_types' => ['css', 'js'],
    'ignore_urls' => [
        '/\/(livewire)\/.*(js)/',
        '/\/(min)\/.*(css|js)/',
        '/^\/(plausible)\/.*(js)/',
    ],
    'minify_enabled' => function(){
        return isset($_GET['no_min']) == false;
    },
    'min_path' => '/assets/min/', // relative to public
];
