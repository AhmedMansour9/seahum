<?php

/*
|--------------------------------------------------------------------------
| Software / Installation Settings
|--------------------------------------------------------------------------
|
| Values written to the .env file by the installer, the software updater and
| the admin "Environment settings" page. They are exposed through the config
| repository so application code never calls env() at runtime, which is what
| makes `php artisan config:cache` safe for this application.
|
| Whenever one of these keys is written back to .env, App\Utils\Environment
| syncs the value here and drops the cached config file.
|
*/

return [

    'purchase_code' => env('PURCHASE_CODE'),

    'buyer_username' => env('BUYER_USERNAME'),

    'admin_name' => env('ADMIN_NAME', ''),

    'admin_identifier' => env('ADMIN_IDENTIFIER', ''),

    'version' => env('SOFTWARE_VERSION'),

    /*
     * Keep the default as true: the activation check treats a missing value as
     * a development environment.
     */
    'development_environment' => env('DEVELOPMENT_ENVIRONMENT', true),

    'web_theme' => env('WEB_THEME'),

    'force_https' => env('FORCE_HTTPS'),

];
