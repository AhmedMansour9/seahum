<?php

$base = require __DIR__.'/scribe.php';

return array_replace($base, [
    'title' => 'Seahum - Delivery API',
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/v2/delivery-man/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [],
        ],
    ],
    'static' => [
        'output_path' => 'docs/postman/scribe/delivery',
    ],
]);
