<?php

return [
    'maps' => [
        'api_key' => env('GOOGLE_API', ''),
    ],
    'cache' => [
        'panels' => env('PANEL_CACHE', true)
    ]
];