<?php

return [
    'service' => 'Turnstile', // options: Recaptcha / Hcaptcha / Turnstile
    'sitekey' => env('CAPTCHA_SITEKEY', ''),
    'secret' => env('CAPTCHA_SECRET', ''),
    'collections' => [],
    'forms' => 'all',
    'user_login' => false,
    'user_registration' => false,
    'disclaimer' => '',
    'invisible' => false,
    'hide_badge' => false,
    'enable_api_routes' => false,
];
