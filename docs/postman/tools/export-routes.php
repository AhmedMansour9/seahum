<?php

/**
 * Exports the authoritative API route inventory to docs/postman/reports/route-inventory.json.
 *
 * Usage: php docs/postman/tools/export-routes.php
 *
 * Reads the router statically: controllers are never instantiated (resolving
 * them pulls in FirebaseServiceProvider, which is why `route:list` fails) and
 * no route is executed. A recording route collection captures every
 * registration, so registrations shadowed by a later identical method + URI
 * are reported instead of silently collapsed.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;

$projectRoot = dirname(__DIR__, 3);

require $projectRoot.'/vendor/autoload.php';

$app = require $projectRoot.'/bootstrap/app.php';

final class RecordingRouteCollection extends RouteCollection
{
    /** @var list<array{route: Route, source: string}> */
    public array $registrations = [];

    public function __construct(private readonly string $projectRoot) {}

    public function add(Route $route): Route
    {
        $this->registrations[] = ['route' => $route, 'source' => $this->registrationSource()];

        return parent::add($route);
    }

    private function registrationSource(): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            $file = $frame['file'] ?? '';
            if (preg_match('#/(Modules/[^/]+/)?routes/.+\.php$#', $file)) {
                return ltrim(str_replace($this->projectRoot, '', $file), '/').':'.$frame['line'];
            }
        }

        return 'unknown';
    }
}

/**
 * Legacy v2 seller handlers authenticate inside the method via Helpers::get_seller_by_token().
 */
function handlerChecksSellerToken(string $controller, string $controllerMethod): bool
{
    $reflection = new ReflectionMethod($controller, $controllerMethod);
    $lines = file($reflection->getFileName());
    $source = implode('', array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1));

    return str_contains($source, 'get_seller_by_token(');
}

$recordingCollection = new RecordingRouteCollection($projectRoot);
$app->make('router')->setRoutes($recordingCollection);
$app->make(Kernel::class)->bootstrap();

$apiClients = [
    '#^api/v1(/|$)#' => ['client' => 'customer', 'version' => 'v1'],
    '#^api/v2/seller(/|$)#' => ['client' => 'vendor', 'version' => 'v2'],
    '#^api/v3/seller(/|$)#' => ['client' => 'vendor', 'version' => 'v3'],
    '#^api/v2/delivery-man(/|$)#' => ['client' => 'delivery', 'version' => 'v2'],
];

$intentionalExclusions = [
    'GET api/v1/taxmodule' => 'TaxModule scaffold stub: inline closure behind auth:sanctum (customers authenticate with Passport), returns the authenticated user only.',
];

$resolveClient = function (string $uri) use ($apiClients): ?array {
    foreach ($apiClients as $pattern => $client) {
        if (preg_match($pattern, $uri)) {
            return $client;
        }
    }

    return null;
};

$resolveAuthentication = function (array $middleware): string {
    return match (true) {
        in_array('auth:api', $middleware, true) => 'customer_token',
        in_array('apiGuestCheck', $middleware, true) => 'customer_token_or_guest_id',
        in_array('seller_api_auth', $middleware, true) => 'seller_token',
        in_array('delivery_man_auth', $middleware, true) => 'deliveryman_token',
        in_array('auth:sanctum', $middleware, true) => 'sanctum',
        default => 'public',
    };
};

$describeRoute = function (Route $route, string $source) use ($resolveClient, $resolveAuthentication): array {
    $action = $route->getAction();
    $uses = is_string($action['uses'] ?? null) ? $action['uses'] : 'Closure';
    $middleware = array_values(array_unique((array) ($action['middleware'] ?? [])));
    [$controller, $controllerMethod] = str_contains($uses, '@') ? explode('@', $uses, 2) : [$uses, null];
    $controllerMethodExists = $controllerMethod === null || method_exists($controller, $controllerMethod);
    $client = $resolveClient($route->uri());
    $authentication = $resolveAuthentication($middleware);
    $authenticationEnforcedBy = $authentication === 'public' ? 'none' : 'middleware';
    if ($authentication === 'public' && $controllerMethod !== null && $controllerMethodExists && handlerChecksSellerToken($controller, $controllerMethod)) {
        $authentication = 'seller_token';
        $authenticationEnforcedBy = 'handler';
    }

    return [
        'methods' => array_values(array_diff($route->methods(), ['HEAD'])),
        'uri' => $route->uri(),
        'name' => $route->getName(),
        'controller' => $controller,
        'controller_method' => $controllerMethod,
        'middleware' => $middleware,
        'api_version' => $client['version'] ?? null,
        'client' => $client['client'] ?? null,
        'business_module' => preg_match('#^Modules\\\\([^\\\\]+)#', $controller, $match) ? $match[1] : 'Core',
        'authentication' => $authentication,
        'authentication_enforced_by' => $authenticationEnforcedBy,
        'registration_source' => $source,
        'controller_method_exists' => $controllerMethodExists,
    ];
};

$effectiveKeys = [];
foreach ($recordingCollection->getRoutes() as $route) {
    $effectiveKeys[spl_object_id($route)] = true;
}

$inventory = [];
$shadowedRegistrations = [];
$totalRoutes = 0;

foreach ($recordingCollection->registrations as $registration) {
    $route = $registration['route'];
    if (! str_starts_with($route->uri(), 'api/')) {
        continue;
    }

    $entry = $describeRoute($route, $registration['source']);

    if (! isset($effectiveKeys[spl_object_id($route)])) {
        $shadowedRegistrations[] = $entry;

        continue;
    }

    $exclusionKey = implode('|', $entry['methods']).' '.$entry['uri'];
    $entry['status'] = match (true) {
        isset($intentionalExclusions[$exclusionKey]) => 'excluded',
        ! $entry['controller_method_exists'] => 'broken',
        default => 'active',
    };
    $entry['note'] = $intentionalExclusions[$exclusionKey]
        ?? ($entry['controller_method_exists'] ? null : "Controller method {$entry['controller']}::{$entry['controller_method']}() does not exist; the route errors at runtime.");

    $inventory[] = $entry;
}

$allRoutesCount = count($recordingCollection->getRoutes());

usort($inventory, fn (array $left, array $right) => [$left['client'], $left['api_version'], $left['uri']] <=> [$right['client'], $right['api_version'], $right['uri']]);

$outputPath = $projectRoot.'/docs/postman/reports/route-inventory.json';
@mkdir(dirname($outputPath), 0755, true);
file_put_contents($outputPath, json_encode([
    'laravel_routes_total' => $allRoutesCount,
    'api_routes_effective' => count($inventory),
    'shadowed_registrations' => $shadowedRegistrations,
    'routes' => $inventory,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

echo 'Laravel routes: '.$allRoutesCount.PHP_EOL;
echo 'Effective API routes: '.count($inventory).PHP_EOL;
echo 'Shadowed API registrations: '.count($shadowedRegistrations).PHP_EOL;
echo 'Written: docs/postman/reports/route-inventory.json'.PHP_EOL;
