<?php

return [
    'file_types' => ['css', 'js'],
    'ignore_urls' => [],
    'minify_enabled' => function(){
        return isset($_GET['no_min']) == false;
    },
    'min_path' => '/assets/min/', // relative to public
];
