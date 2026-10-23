<?php

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;

/**
 * Shared, side-effect-free Scribe base configuration.
 *
 * It matches no routes on its own. The per-client configs
 * (scribe_customer, scribe_vendor, scribe_delivery) require this file and
 * override the title, route matching and output path.
 *
 * Safety: several API GET routes mutate state (order placement, refunds,
 * account deletion...). ResponseCalls is therefore removed entirely, and every
 * response strategy that instantiates models or factories is removed too, so
 * generation never executes an endpoint and never writes to the database.
 *
 * Scribe is a dev dependency; without it (composer install --no-dev) this
 * config resolves to an empty array so the application still boots.
 */
if (! class_exists(Defaults::class)) {
    return [];
}

return [
    'title' => 'Seahum API',
    'description' => 'Statically extracted API reference. Responses are not captured from live calls.',
    'intro_text' => <<<'INTRO'
        Generated statically from the Laravel route definitions, FormRequests and inline validation.
        No endpoint was executed while generating this documentation.
    INTRO,
    'base_url' => env('SCRIBE_BASE_URL', 'http://localhost'),

    'routes' => [],

    'type' => 'static',
    'theme' => 'default',
    'static' => [
        'output_path' => 'docs/postman/scribe/base',
    ],
    'laravel' => [
        'add_routes' => false,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],
    'external' => [
        'html_attributes' => [],
    ],
    'try_it_out' => [
        'enabled' => false,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],
    'auth' => [
        'enabled' => true,
        'default' => false,
        'in' => AuthIn::BEARER->value,
        'name' => 'Authorization',
        'use_value' => null,
        'placeholder' => '{YOUR_TOKEN}',
        'extra_info' => '',
    ],
    'example_languages' => ['bash'],
    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],
    'openapi' => [
        'enabled' => true,
        'version' => '3.0.3',
        'overrides' => [],
        'generators' => [],
    ],
    'groups' => [
        'default' => 'Endpoints',
        'order' => [],
    ],
    'logo' => false,
    'last_updated' => 'Last updated: {date:F j, Y}',
    'examples' => [
        'faker_seed' => 1234,
        'models_source' => [],
    ],
    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => [
            Strategies\Responses\UseResponseTag::class,
            Strategies\Responses\UseResponseFileTag::class,
        ],
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ],
    ],
    'database_connections_to_transact' => [config('database.default')],
    'fractal' => [
        'serializer' => null,
    ],
];
