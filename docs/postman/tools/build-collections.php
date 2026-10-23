<?php

/**
 * Builds the curated Postman collections, environments, OpenAPI copies and
 * coverage reports from:
 *   - docs/postman/reports/route-inventory.json  (export-routes.php)
 *   - docs/postman/scribe/.cache/<set>/endpoints/*.yaml  (generate-scribe.php)
 *   - a static scan of each controller method's source for request inputs
 *
 * Usage: php docs/postman/tools/build-collections.php
 *
 * Pure file transformation: it does not boot Laravel, call endpoints or touch
 * the database. Output is deterministic (stable ids, sorted items), so
 * re-running it with the same inputs produces identical files.
 */

use Symfony\Component\Yaml\Yaml;

$projectRoot = dirname(__DIR__, 3);
$docsRoot = $projectRoot.'/docs/postman';

require $projectRoot.'/vendor/autoload.php';

$collectionDefinitions = require __DIR__.'/collection-definitions.php';

$inventory = json_decode(file_get_contents($docsRoot.'/reports/route-inventory.json'), true, flags: JSON_THROW_ON_ERROR);

function stableUuid(string $seed): string
{
    $hash = md5($seed);

    return sprintf('%s-%s-4%s-a%s-%s', substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 13, 3), substr($hash, 17, 3), substr($hash, 20, 12));
}

/**
 * @return array<string, array<string, mixed>> Scribe endpoints keyed by "METHOD uri".
 */
function loadScribeEndpoints(string $intermediateDirectory): array
{
    $endpoints = [];
    foreach (glob($intermediateDirectory.'/endpoints/*.yaml') as $endpointFile) {
        $group = Yaml::parseFile($endpointFile);
        foreach ($group['endpoints'] ?? [] as $endpoint) {
            foreach ($endpoint['httpMethods'] as $httpMethod) {
                $endpoints[$httpMethod.' '.$endpoint['uri']] = $endpoint;
            }
        }
    }

    return $endpoints;
}

/**
 * Realistic, non-production placeholder for a parameter, chosen by name and type.
 */
function placeholderValue(string $leafName, string $type, array $enumValues, string $collectionKey): mixed
{
    if ($enumValues !== []) {
        return $enumValues[0];
    }

    $name = strtolower($leafName);
    $baseType = rtrim($type, '[]');

    return match (true) {
        $baseType === 'boolean' => true,
        str_contains($name, 'email') && ! str_contains($name, 'phone') => $collectionKey === 'vendor' ? 'seller@gmail.com' : 'buyer@gmail.com',
        $name === 'email_or_phone' => $collectionKey === 'vendor' ? 'seller@gmail.com' : 'buyer@gmail.com',
        str_contains($name, 'password') => 'password',
        str_contains($name, 'phone') => '+971500000000',
        str_contains($name, 'currency') => 'AED',
        preg_match('/(price|amount|cost|balance|total)/', $name) === 1 => 1000,
        str_contains($name, 'lat') && ! str_contains($name, 'late') => 25.2048,
        str_contains($name, 'lon') || str_contains($name, 'lng') => 55.2708,
        preg_match('/(^|_)(id|ids)$/', $name) === 1 => 1,
        preg_match('/(limit|per_page)/', $name) === 1 => 10,
        preg_match('/(offset|page)$/', $name) === 1 => 1,
        str_contains($name, 'date') => '2026-01-01',
        $name === 'token' || $name === 'otp' || str_ends_with($name, '_otp') || $name === 'reset_token' => '{{otp_or_token}}',
        str_contains($name, 'slug') => 'sample-slug',
        preg_match('/(quantity|qty|stock|rating|discount|tax)$/', $name) === 1 => 1,
        $baseType === 'integer' => 1,
        $baseType === 'number' => 1,
        $baseType === 'object' => new stdClass,
        str_contains($name, 'name') => 'Test',
        str_contains($name, 'address') => 'Dubai, UAE',
        str_contains($name, 'country') => 'United Arab Emirates',
        str_contains($name, 'city') => 'Dubai',
        str_contains($name, 'zip') => '00000',
        str_contains($name, 'lang') => 'en',
        default => 'string',
    };
}

function scalarToString(mixed $value): string
{
    return match (true) {
        is_bool($value) => $value ? '1' : '0',
        is_scalar($value) => (string) $value,
        default => '',
    };
}

/**
 * Converts flat Scribe parameter names (a.b, a[].b, a[]) into a nested example payload.
 *
 * @param  array<string, array<string, mixed>>  $parameters
 */
function buildNestedPayload(array $parameters, string $collectionKey): array
{
    $payload = [];
    foreach ($parameters as $fullName => $parameter) {
        $segments = preg_split('/\./', str_replace('[]', '.[]', $fullName));
        $leafName = '';
        foreach (array_reverse($segments) as $segment) {
            if ($segment !== '[]') {
                $leafName = $segment;
                break;
            }
        }
        $value = placeholderValue($leafName, $parameter['type'] ?? 'string', $parameter['enumValues'] ?? [], $collectionKey);
        if (str_ends_with($parameter['type'] ?? '', '[]') && end($segments) !== '[]') {
            $value = [$value];
        }

        $cursor = &$payload;
        foreach ($segments as $index => $segment) {
            $isLast = $index === array_key_last($segments);
            $key = $segment === '[]' ? 0 : $segment;
            if ($isLast) {
                if (! isset($cursor[$key]) || ! is_array($cursor[$key])) {
                    $cursor[$key] = $value;
                }
            } else {
                if (! isset($cursor[$key]) || ! is_array($cursor[$key])) {
                    $cursor[$key] = [];
                }
                $cursor = &$cursor[$key];
            }
        }
        unset($cursor);
    }

    return $payload;
}

/**
 * Flattens a nested payload into multipart form keys: a[0][b].
 *
 * @return list<array{key: string, value: mixed}>
 */
function flattenToFormKeys(array $payload, string $prefix = ''): array
{
    $pairs = [];
    foreach ($payload as $key => $value) {
        $formKey = $prefix === '' ? (string) $key : "{$prefix}[{$key}]";
        if (is_array($value) && $value !== []) {
            array_push($pairs, ...flattenToFormKeys($value, $formKey));
        } else {
            $pairs[] = ['key' => $formKey, 'value' => $value];
        }
    }

    return $pairs;
}

/**
 * Statically scans one controller method for request inputs and write calls.
 *
 * @return array{inputs: list<string>, files: list<string>, writes: list<string>}
 */
function scanControllerMethod(?string $controller, ?string $method): array
{
    $result = ['inputs' => [], 'files' => [], 'writes' => []];
    if ($controller === null || $method === null || ! method_exists($controller, $method)) {
        return $result;
    }

    $reflection = new ReflectionMethod($controller, $method);
    $lines = file($reflection->getFileName());
    $source = implode('', array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1));

    preg_match_all('/\$request->(?:input|get|query|post|has|filled|boolean|integer|string|date)\(\s*[\'"]([A-Za-z0-9_.\-]+)[\'"]/', $source, $calls);
    preg_match_all('/\$request\[\s*[\'"]([A-Za-z0-9_\-]+)[\'"]\s*\]/', $source, $arrayAccess);
    preg_match_all('/\$request->(?!validate|all|only|except|user|file|hasFile|header|bearerToken|route|ip|merge|method|isMethod|is|url|fullUrl|path|query|input|get|post|has|filled|boolean|integer|string|date|wantsJson|expectsJson|ajax|server|cookie|session|validated|safe|missing|exists|keys)([a-z_][A-Za-z0-9_]*)\b(?!\s*\()/', $source, $properties);
    preg_match_all('/\$request->(?:file|hasFile)\(\s*[\'"]([A-Za-z0-9_\-]+)[\'"]/', $source, $files);
    preg_match_all('/((?:->|::)(?:save|insert|increment|decrement|forceDelete|destroy|attach|detach|sync)\(|(?:->|::)(?:create|update|delete|remove|store|add|place|cancel|generate)(?!Select)(?:[A-Z_]\w*)?\(|DB::table\()/', $source, $writes);

    $ignoredNames = ['seller', 'delivery_man', 'user', 'guest_id'];
    $result['inputs'] = array_values(array_diff(array_unique([...$calls[1], ...$arrayAccess[1], ...$properties[1]]), $ignoredNames));
    $result['files'] = array_values(array_unique($files[1]));
    $result['writes'] = array_values(array_unique($writes[1]));
    sort($result['inputs']);

    return $result;
}

function resolveFolder(string $relativePath, array $folderRules): array
{
    foreach ($folderRules as [$pattern, $folderPath]) {
        if (preg_match($pattern, $relativePath)) {
            return $folderPath;
        }
    }

    throw new RuntimeException("No folder rule matches '{$relativePath}'. Add one to collection-definitions.php.");
}

function buildRequestItem(array $route, ?array $scribeEndpoint, array $definition, string $collectionKey): array
{
    $relativePath = substr($route['uri'], strlen($definition['uri_prefix']));
    $methods = $route['methods'];
    $httpMethod = count($methods) > 1 ? 'GET' : $methods[0];
    $scan = scanControllerMethod($route['controller'] === 'Closure' ? null : $route['controller'], $route['controller_method']);

    $pathSegments = array_map(
        fn (string $segment) => preg_replace('/^\{(\w+)\??\}$/', ':$1', $segment),
        explode('/', $relativePath)
    );

    $documentedBody = $scribeEndpoint['bodyParameters'] ?? [];
    $documentedQuery = $scribeEndpoint['queryParameters'] ?? [];
    foreach ($scan['files'] as $fileInputName) {
        if (isset($documentedBody[$fileInputName])) {
            $documentedBody[$fileInputName]['type'] = 'file';
        }
    }
    $fileParameters = array_filter($documentedBody, fn (array $parameter) => str_starts_with($parameter['type'] ?? '', 'file'));
    if ($httpMethod === 'GET') {
        $documentedQuery += $documentedBody;
        $documentedBody = [];
    }

    $knownNames = array_map(fn (string $name) => explode('.', str_replace('[]', '', $name))[0], array_keys($documentedBody + $documentedQuery));
    $urlParameterNames = array_keys($scribeEndpoint['urlParameters'] ?? []);
    preg_match_all('/\{(\w+)\??\}/', $route['uri'], $uriParameters);
    $undocumentedInputs = array_values(array_diff($scan['inputs'], $knownNames, $urlParameterNames, $uriParameters[1]));
    $undocumentedFiles = array_values(array_diff($scan['files'], $knownNames));

    $query = [];
    foreach ($documentedQuery as $name => $parameter) {
        if (str_contains($name, '.') || str_contains($name, '[]')) {
            continue;
        }
        $query[] = [
            'key' => $name,
            'value' => scalarToString(placeholderValue($name, $parameter['type'] ?? 'string', $parameter['enumValues'] ?? [], $collectionKey)),
            'description' => trim(($parameter['required'] ? 'Required. ' : 'Optional. ').($parameter['type'] ?? '').'. '.($parameter['description'] ?? '')),
            'disabled' => ! $parameter['required'],
        ];
    }
    if ($httpMethod === 'GET') {
        foreach ($undocumentedInputs as $name) {
            $query[] = ['key' => $name, 'value' => '', 'description' => 'Read by the controller without declared validation (static scan). Optional unless the handler requires it.', 'disabled' => true];
        }
    }
    if ($route['authentication'] === 'customer_token_or_guest_id') {
        $query[] = ['key' => 'guest_id', 'value' => '{{guest_id}}', 'description' => 'Guest flow: used when no customer Bearer token is sent. Obtain one from GET /get-guest-id.', 'disabled' => false];
    }

    $headers = [['key' => 'Accept', 'value' => 'application/json']];
    if (in_array('api_lang', $route['middleware'], true)) {
        $headers[] = ['key' => 'lang', 'value' => '{{lang}}', 'description' => 'Locale for translated messages (APILocalizationMiddleware).'];
    }

    $body = null;
    if ($httpMethod !== 'GET') {
        $bodyParameterDefinitions = $documentedBody;
        foreach ($undocumentedInputs as $name) {
            $bodyParameterDefinitions[$name] ??= ['type' => 'string', 'required' => false, 'enumValues' => []];
        }
        foreach ($undocumentedFiles as $name) {
            $bodyParameterDefinitions[$name] ??= ['type' => 'file', 'required' => false, 'enumValues' => []];
        }
        $hasFiles = $fileParameters !== [] || $undocumentedFiles !== [];

        if ($bodyParameterDefinitions !== [] && $hasFiles) {
            $formData = [];
            $nonFileDefinitions = array_filter($bodyParameterDefinitions, fn (array $parameter) => ! str_starts_with($parameter['type'] ?? '', 'file'));
            foreach (flattenToFormKeys(buildNestedPayload($nonFileDefinitions, $collectionKey)) as $pair) {
                $formData[] = ['key' => $pair['key'], 'value' => scalarToString($pair['value']), 'type' => 'text'];
            }
            foreach ($bodyParameterDefinitions as $name => $parameter) {
                if (str_starts_with($parameter['type'] ?? '', 'file')) {
                    $formKey = preg_replace('/\.(\w+)/', '[$1]', str_replace('[]', '[0]', $name));
                    $formData[] = ['key' => $formKey, 'type' => 'file', 'src' => [], 'description' => ($parameter['required'] ?? false) ? 'Required file.' : 'Optional file.'];
                }
            }
            $body = ['mode' => 'formdata', 'formdata' => $formData];
        } elseif ($bodyParameterDefinitions !== []) {
            $body = [
                'mode' => 'raw',
                'raw' => json_encode(buildNestedPayload($bodyParameterDefinitions, $collectionKey), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'options' => ['raw' => ['language' => 'json']],
            ];
            $headers[] = ['key' => 'Content-Type', 'value' => 'application/json'];
        }
    }

    $authKey = $definition['auth_by_requirement'][$route['authentication']] ?? null;
    $requestAuth = $authKey === null ? ['type' => 'noauth'] : null;

    $parameterDocs = [];
    foreach ($documentedBody + $documentedQuery as $name => $parameter) {
        $rules = trim(($parameter['type'] ?? '').' '.($parameter['description'] ?? ''));
        $parameterDocs[] = sprintf('| `%s` | %s | %s |', $name, ($parameter['required'] ?? false) ? 'yes' : 'no', str_replace('|', '\\|', $rules));
    }

    $descriptionLines = array_filter([
        $route['status'] === 'broken' ? "> **Broken route:** {$route['note']}" : null,
        $scan['writes'] !== [] && $httpMethod === 'GET' ? '> **Possible state-changing GET:** a static scan of the handler found write-like calls ('.implode(', ', array_map(fn ($write) => '`'.$write.'`', $scan['writes'])).'). Treat it as unsafe against shared data until verified.' : null,
        count($methods) > 1 ? '> Registered with `Route::any`: accepts '.implode(', ', $methods).'. Documented once as GET.' : null,
        ($scribeEndpoint['metadata']['title'] ?? '') !== '' ? $scribeEndpoint['metadata']['title'] : null,
        ($scribeEndpoint['metadata']['description'] ?? '') !== '' ? $scribeEndpoint['metadata']['description'] : null,
        '**Route:** `'.implode('|', $methods).' /'.$route['uri'].'`'.($route['name'] ? " (`{$route['name']}`)" : ''),
        '**Authentication:** '.$definition['auth_labels'][$route['authentication']],
        '**Handler:** `'.$route['controller'].($route['controller_method'] ? '@'.$route['controller_method'] : '').'`',
        '**Middleware:** `'.implode('`, `', $route['middleware']).'`',
        '**Registered in:** `'.$route['registration_source'].'`',
        $parameterDocs !== [] ? "\n| Parameter | Required | Rules |\n|---|---|---|\n".implode("\n", $parameterDocs) : null,
        $undocumentedInputs !== [] ? "\n**Inputs read without declared validation (static scan):** `".implode('`, `', $undocumentedInputs).'`' : null,
        "\n**Responses:** not captured. No endpoint was executed while generating this collection; the shape is defined by the handler above. Validation failures in this codebase typically return `403` with `{\"errors\": [{\"code\", \"message\"}]}`.",
    ]);

    $enabledQuery = array_filter($query, fn (array $parameter) => ! $parameter['disabled']);

    $item = [
        'id' => stableUuid($collectionKey.' '.implode('|', $methods).' '.$route['uri']),
        'name' => $relativePath,
        'request' => [
            'method' => $httpMethod,
            'header' => $headers,
            'url' => [
                'raw' => '{{base_url}}/'.implode('/', $pathSegments).($enabledQuery !== [] ? '?'.implode('&', array_map(fn ($parameter) => $parameter['key'].'='.$parameter['value'], $enabledQuery)) : ''),
                'host' => ['{{base_url}}'],
                'path' => $pathSegments,
            ],
            'description' => implode("\n\n", $descriptionLines),
        ],
        'response' => [],
    ];

    if ($query !== []) {
        $item['request']['url']['query'] = $query;
    }

    $urlVariables = [];
    foreach ($uriParameters[1] as $parameterName) {
        $parameter = $scribeEndpoint['urlParameters'][$parameterName] ?? ['type' => 'string', 'description' => ''];
        $urlVariables[] = [
            'key' => $parameterName,
            'value' => scalarToString(placeholderValue($parameterName, $parameter['type'] ?? 'string', [], $collectionKey)),
            'description' => trim(($parameter['type'] ?? 'string').'. '.($parameter['description'] ?? '')),
        ];
    }
    if ($urlVariables !== []) {
        $item['request']['url']['variable'] = $urlVariables;
    }
    if ($body !== null) {
        $item['request']['body'] = $body;
    }
    if ($requestAuth !== null) {
        $item['request']['auth'] = $requestAuth;
    }

    $tokenVariable = $definition['token_capture'][$route['uri']] ?? null;
    if ($tokenVariable !== null) {
        $item['event'] = [[
            'listen' => 'test',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    "pm.test('Login succeeded (200)', function () { pm.response.to.have.status(200); });",
                    'if (pm.response.code === 200) {',
                    '    const responseBody = pm.response.json();',
                    "    pm.test('Response contains token', function () { pm.expect(responseBody.token).to.be.a('string'); });",
                    "    if (responseBody.token) { pm.environment.set('{$tokenVariable}', responseBody.token); }",
                    '}',
                ],
            ],
        ]];
    }

    return $item;
}

function insertIntoFolders(array &$tree, array $folderPath, array $item): void
{
    $cursor = &$tree;
    foreach ($folderPath as $folderName) {
        $cursor[$folderName] ??= [];
        $cursor = &$cursor[$folderName];
    }
    $cursor['__requests'][] = $item;
}

function folderTreeToItems(array $tree, array $folderOrder, string $seed): array
{
    $items = [];
    $folderNames = array_filter(array_keys($tree), fn ($key) => $key !== '__requests');
    usort($folderNames, function ($left, $right) use ($folderOrder) {
        $leftPosition = array_search($left, $folderOrder, true);
        $rightPosition = array_search($right, $folderOrder, true);

        return [$leftPosition === false ? PHP_INT_MAX : $leftPosition, $left] <=> [$rightPosition === false ? PHP_INT_MAX : $rightPosition, $right];
    });

    foreach ($folderNames as $folderName) {
        $items[] = [
            'id' => stableUuid($seed.'/'.$folderName),
            'name' => $folderName,
            'item' => folderTreeToItems($tree[$folderName], $folderOrder, $seed.'/'.$folderName),
        ];
    }

    $requests = $tree['__requests'] ?? [];
    usort($requests, fn ($left, $right) => [$left['name'], $left['request']['method']] <=> [$right['name'], $right['request']['method']]);

    return [...$items, ...$requests];
}

function collectRequests(array $items, array $folderPath = []): array
{
    $requests = [];
    foreach ($items as $item) {
        if (isset($item['item'])) {
            array_push($requests, ...collectRequests($item['item'], [...$folderPath, $item['name']]));
        } else {
            $requests[] = ['folder' => implode(' / ', $folderPath), 'item' => $item];
        }
    }

    return $requests;
}

$jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
$coverage = [];
$unresolved = [];

foreach ($collectionDefinitions['collections'] as $collectionKey => $definition) {
    $scribeEndpoints = loadScribeEndpoints($docsRoot.'/scribe/.cache/'.$collectionKey);
    $routes = array_values(array_filter($inventory['routes'], fn (array $route) => $route['client'] === $collectionKey));

    $folderTree = [];
    $expected = [];
    $excluded = [];
    foreach ($routes as $route) {
        $routeKey = implode('|', $route['methods']).' '.$route['uri'];
        if ($route['status'] === 'excluded') {
            $excluded[] = ['route' => $routeKey, 'reason' => $route['note']];

            continue;
        }

        $expected[] = $routeKey;
        $scribeEndpoint = $scribeEndpoints[$route['methods'][0].' '.$route['uri']] ?? null;
        if ($scribeEndpoint === null && $route['status'] !== 'broken') {
            $unresolved[] = "{$collectionKey}: Scribe produced no extraction for {$routeKey}; request built from the route definition only.";
        }

        $relativePath = substr($route['uri'], strlen($definition['uri_prefix']));
        insertIntoFolders($folderTree, resolveFolder($relativePath, $definition['folder_rules']), buildRequestItem($route, $scribeEndpoint, $definition, $collectionKey));
    }

    $collection = [
        'info' => [
            '_postman_id' => stableUuid('collection '.$collectionKey),
            'name' => $definition['name'],
            'description' => $definition['description'],
            'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
        ],
        'item' => folderTreeToItems($folderTree, $definition['folder_order'], $collectionKey),
        'auth' => [
            'type' => 'bearer',
            'bearer' => [['key' => 'token', 'value' => '{{'.$definition['token_variable'].'}}', 'type' => 'string']],
        ],
        'variable' => [
            ['key' => 'base_url', 'value' => $definition['base_url'], 'type' => 'string'],
        ],
    ];

    $collectionPath = $docsRoot.'/collections/'.$definition['file'];
    @mkdir(dirname($collectionPath), 0755, true);
    file_put_contents($collectionPath, json_encode($collection, $jsonFlags).PHP_EOL);

    $documented = [];
    $duplicates = [];
    foreach (collectRequests($collection['item']) as $request) {
        $path = implode('/', array_map(fn ($segment) => preg_replace('/^:(\w+)$/', '{$1}', $segment), $request['item']['request']['url']['path']));
        $matchingRoute = null;
        foreach ($routes as $route) {
            if ($route['uri'] === $definition['uri_prefix'].$path && in_array($request['item']['request']['method'], $route['methods'], true)) {
                $matchingRoute = implode('|', $route['methods']).' '.$route['uri'];
            }
        }
        $documentedKey = $matchingRoute ?? $request['item']['request']['method'].' '.$definition['uri_prefix'].$path.' (no matching route)';
        if (isset($documented[$documentedKey])) {
            $duplicates[] = $documentedKey;
        }
        $documented[$documentedKey] = $request['folder'];
    }

    $coverage[$collectionKey] = [
        'name' => $definition['name'],
        'routes' => count($routes),
        'expected' => $expected,
        'excluded' => $excluded,
        'documented' => $documented,
        'missing' => array_values(array_diff($expected, array_keys($documented))),
        'extra' => array_values(array_diff(array_keys($documented), $expected)),
        'duplicates' => $duplicates,
        'broken' => array_values(array_map(fn ($route) => implode('|', $route['methods']).' '.$route['uri'].' — '.$route['note'], array_filter($routes, fn ($route) => $route['status'] === 'broken'))),
        'by_version' => array_count_values(array_map(fn ($route) => $route['api_version'], $routes)),
    ];

    $openApiSource = $docsRoot.'/scribe/'.$collectionKey.'/openapi.yaml';
    $openApi = Yaml::parseFile($openApiSource, Yaml::PARSE_OBJECT_FOR_MAP);
    $openApi->servers = [(object) ['url' => '{api_host}', 'variables' => (object) ['api_host' => (object) ['default' => 'http://localhost']]]];
    $seenOperationIds = [];
    foreach (get_object_vars($openApi->paths) as $pathName => $pathItem) {
        foreach (get_object_vars($pathItem) as $httpVerb => $operation) {
            if (is_object($operation) && isset($operation->operationId)) {
                if (isset($seenOperationIds[$operation->operationId])) {
                    $operation->operationId = $httpVerb.str_replace(' ', '', ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', $pathName)));
                }
                $seenOperationIds[$operation->operationId] = true;
            }
            if (is_object($operation) && (array) ($operation->responses ?? []) === []) {
                $operation->responses = (object) ['default' => (object) ['description' => 'Response not captured: documentation is generated statically without calling the endpoint.']];
            }
        }
    }
    @mkdir($docsRoot.'/openapi', 0755, true);
    file_put_contents($docsRoot.'/openapi/'.$collectionKey.'.yaml', Yaml::dump($openApi, 14, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE));
}

foreach ($collectionDefinitions['environments'] as $environmentFile => $environment) {
    $values = [];
    foreach ($environment['values'] as $key => $value) {
        $values[] = ['key' => $key, 'value' => $value, 'type' => str_ends_with($key, '_token') ? 'secret' : 'default', 'enabled' => true];
    }
    @mkdir($docsRoot.'/environments', 0755, true);
    file_put_contents($docsRoot.'/environments/'.$environmentFile, json_encode([
        'id' => stableUuid('environment '.$environment['name']),
        'name' => $environment['name'],
        'values' => $values,
        '_postman_variable_scope' => 'environment',
    ], $jsonFlags).PHP_EOL);
}

$totalExpected = array_sum(array_map(fn ($set) => count($set['expected']), $coverage));
$totalCovered = array_sum(array_map(fn ($set) => count(array_intersect($set['expected'], array_keys($set['documented']))), $coverage));
$totalMissing = array_sum(array_map(fn ($set) => count($set['missing']), $coverage));
$totalExcluded = array_sum(array_map(fn ($set) => count($set['excluded']), $coverage));
$coveragePercentage = $totalExpected === 0 ? 0 : round($totalCovered / $totalExpected * 100, 2);

$report = [];
$report[] = '# API Coverage Report';
$report[] = '';
$report[] = 'Generated by `docs/postman/tools/build-collections.php`. Static contract coverage only: requests were **not** executed.';
$report[] = '';
$report[] = 'Normalization: one entry per effective Laravel route (method set + URI). `Route::any` routes count once and are documented as GET. Shadowed duplicate registrations are collapsed by the router and listed separately.';
$report[] = '';
$report[] = '| Metric | Value |';
$report[] = '|---|---:|';
$report[] = '| Laravel routes (all) | '.$inventory['laravel_routes_total'].' |';
$report[] = '| Effective API routes | '.$inventory['api_routes_effective'].' |';
$report[] = '| Shadowed duplicate registrations | '.count($inventory['shadowed_registrations']).' |';
$report[] = '| Customer routes (v1) | '.$coverage['customer']['routes'].' |';
$report[] = '| Vendor v2 routes | '.($coverage['vendor']['by_version']['v2'] ?? 0).' |';
$report[] = '| Vendor v3 routes | '.($coverage['vendor']['by_version']['v3'] ?? 0).' |';
$report[] = '| Delivery routes (v2) | '.$coverage['delivery']['routes'].' |';
$report[] = '| Documented requests | '.array_sum(array_map(fn ($set) => count($set['documented']), $coverage)).' |';
$report[] = '| Intentional exclusions | '.$totalExcluded.' |';
$report[] = '| Missing endpoints | '.$totalMissing.' |';
$report[] = '| Ambiguous / broken endpoints | '.array_sum(array_map(fn ($set) => count($set['broken']), $coverage)).' |';
$report[] = '| Duplicate requests | '.array_sum(array_map(fn ($set) => count($set['duplicates']), $coverage)).' |';
$report[] = '| Coverage | '.$coveragePercentage.'% ('.$totalCovered.' / '.$totalExpected.') |';
$report[] = '';
foreach ($coverage as $set) {
    $report[] = '## '.$set['name'];
    $report[] = '';
    $report[] = '- Routes: '.$set['routes'].', expected: '.count($set['expected']).', documented: '.count($set['documented']);
    $report[] = '- Missing: '.($set['missing'] === [] ? 'none' : '`'.implode('`, `', $set['missing']).'`');
    $report[] = '- Extra (no matching route): '.($set['extra'] === [] ? 'none' : '`'.implode('`, `', $set['extra']).'`');
    $report[] = '- Duplicates: '.($set['duplicates'] === [] ? 'none' : '`'.implode('`, `', $set['duplicates']).'`');
    foreach ($set['excluded'] as $exclusion) {
        $report[] = '- Excluded: `'.$exclusion['route'].'` — '.$exclusion['reason'];
    }
    foreach ($set['broken'] as $broken) {
        $report[] = '- Broken (documented, flagged): `'.$broken.'`';
    }
    $folderCounts = array_count_values($set['documented']);
    ksort($folderCounts);
    $report[] = '';
    $report[] = '| Folder | Requests |';
    $report[] = '|---|---:|';
    foreach ($folderCounts as $folder => $count) {
        $report[] = "| {$folder} | {$count} |";
    }
    $report[] = '';
}
$report[] = '## Shadowed duplicate registrations';
$report[] = '';
foreach ($inventory['shadowed_registrations'] as $shadowed) {
    $report[] = '- `'.implode('|', $shadowed['methods']).' '.$shadowed['uri'].'` at `'.$shadowed['registration_source'].'` is overridden by a later registration of the same method + URI.';
}
file_put_contents($docsRoot.'/reports/coverage-report.md', implode("\n", $report).PHP_EOL);

$unresolvedReport = ['# Unresolved Endpoints', ''];
foreach ($coverage as $set) {
    foreach ($set['broken'] as $broken) {
        $unresolvedReport[] = '- **Broken route** ('.$set['name'].'): `'.$broken.'`. Included in the collection and flagged; fix the route or the controller.';
    }
    foreach ($set['missing'] as $missing) {
        $unresolvedReport[] = '- **Missing from collection** ('.$set['name'].'): `'.$missing.'`.';
    }
    foreach ($set['excluded'] as $exclusion) {
        $unresolvedReport[] = '- **Intentionally excluded** ('.$set['name'].'): `'.$exclusion['route'].'`. '.$exclusion['reason'];
    }
}
foreach ($inventory['shadowed_registrations'] as $shadowed) {
    $unresolvedReport[] = '- **Duplicate registration**: `'.implode('|', $shadowed['methods']).' '.$shadowed['uri'].'` at `'.$shadowed['registration_source'].'` is dead code (shadowed).';
}
foreach ($unresolved as $line) {
    $unresolvedReport[] = '- '.$line;
}
$unresolvedReport[] = '- **Responses**: no response bodies are documented. Handlers return ad-hoc arrays (no API Resources / @response tags), and response calls are disabled by design.';
file_put_contents($docsRoot.'/reports/unresolved-endpoints.md', implode("\n", $unresolvedReport).PHP_EOL);

echo "Coverage: {$coveragePercentage}% ({$totalCovered}/{$totalExpected}), missing {$totalMissing}, excluded {$totalExcluded}".PHP_EOL;
foreach ($coverage as $collectionKey => $set) {
    echo "{$collectionKey}: routes={$set['routes']} documented=".count($set['documented']).' missing='.count($set['missing']).' extra='.count($set['extra']).' duplicates='.count($set['duplicates']).PHP_EOL;
}

exit($totalMissing > 0 ? 1 : 0);
