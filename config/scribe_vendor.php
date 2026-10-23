<?php

$base = require __DIR__.'/scribe.php';

return array_replace($base, [
    'title' => 'Seahum - Vendor API',
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/v2/seller/*', 'api/v3/seller/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [],
        ],
    ],
    'static' => [
        'output_path' => 'docs/postman/scribe/vendor',
    ],
]);
