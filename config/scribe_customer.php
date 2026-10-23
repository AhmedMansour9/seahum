<?php

$base = require __DIR__.'/scribe.php';

return array_replace($base, [
    'title' => 'Seahum - Customer API',
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/v1/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => ['GET api/v1/taxmodule'],
        ],
    ],
    'static' => [
        'output_path' => 'docs/postman/scribe/customer',
    ],
]);
