<?php

return [
    'enabled_routes' => [

    ],
    'android_paths' => [

    ],
    'ios_paths' => [
        '/',
        '/product/*',
        '/track-order/*',
    ],

    /*
     * Landing page that sends visitors to Google Play or the App Store. Referral invites share it,
     * because a new customer has to install the app before signing up with the code.
     */
    'app_download_url' => env('APP_DOWNLOAD_URL', 'https://app.sea-hum.store'),
];
