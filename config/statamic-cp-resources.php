
<?php

return [

    'trello_url' => '',

    /**
     * Configure Loom videos to be displayed on the dashboard.
     *
     * The embed_url is the *almost* the same as the sharing link Loom gives you.
     * Except instead of `/share/`, it's `/embed`.
     */
    'looms' => [
        // [
        //     'name' => 'Tour of the Statamic Control Panel',
        //     'embed_url' => 'https://www.loom.com/embed/55d55888vb7540c69c0e46e5ddeb6999',
        // ],
    ],

    'additional_resources' => [
        [
            'name' => 'Documentation will follow',
            'url' => '#',
        ],
    ],

    // allow control over navigation title
    'nav' => [
        'name' => 'thoughtco.',
        'prefix' => 'thoughtco',
        'title' => 'Useful Information',
    ],

];
