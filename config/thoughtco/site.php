<?php

return [
    'maps' => [
        'api_key' => env('GOOGLE_API', ''),
    ],
    'cache' => [
        'panels' => env('TC_PANEL_CACHE', true)
    ]
];