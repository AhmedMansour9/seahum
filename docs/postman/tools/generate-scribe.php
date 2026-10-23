<?php

/**
 * Runs Scribe for the customer, vendor and delivery configs.
 *
 * Usage: php docs/postman/tools/generate-scribe.php [customer|vendor|delivery ...]
 *
 * This wrapper exists only to register a documentation-time FormRequest
 * factory: some FormRequest::rules() read $this->user()->id, so Scribe gets a
 * non-persisted placeholder user (never saved, never queried). No application
 * code is changed and no endpoint is executed (ResponseCalls is disabled in
 * config/scribe.php).
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Knuckles\Scribe\Scribe;

$projectRoot = dirname(__DIR__, 3);

require $projectRoot.'/vendor/autoload.php';

$app = require $projectRoot.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! $app->environment('local', 'testing')) {
    fwrite(STDERR, "Refusing to run outside the local/testing environment.\n");
    exit(1);
}

Scribe::instantiateFormRequestUsing(function (string $formRequestClass) {
    $formRequest = new $formRequestClass;
    $userModel = config('auth.providers.users.model');
    $placeholderUser = (new $userModel)->forceFill(['id' => 0]);
    $formRequest->setUserResolver(fn () => $placeholderUser);

    return $formRequest;
});

$documentationSets = array_slice($argv, 1) ?: ['customer', 'vendor', 'delivery'];
$exitCode = 0;

foreach ($documentationSets as $documentationSet) {
    $status = Artisan::call('scribe:generate', [
        '--config' => "scribe_{$documentationSet}",
        '--scribe-dir' => "docs/postman/scribe/.cache/{$documentationSet}",
        '--no-upgrade-check' => true,
        '--force' => true,
        '--no-interaction' => true,
    ]);

    echo Artisan::output();
    echo "[{$documentationSet}] exit={$status}".PHP_EOL;
    $exitCode = max($exitCode, $status);
}

exit($exitCode);
