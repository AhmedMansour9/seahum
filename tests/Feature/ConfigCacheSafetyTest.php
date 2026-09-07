<?php

namespace Tests\Feature;

use App\Utils\EnvironmentManager;
use Tests\TestCase;

/**
 * `php artisan config:cache` skips loading the .env file, so any runtime env()
 * call returns null (or its default) once the config is cached. Deployments run
 * config:cache, so these guarantees have to hold.
 */
class ConfigCacheSafetyTest extends TestCase
{
    private const SCANNED_DIRECTORIES = ['app', 'Modules', 'resources', 'routes'];

    /**
     * Paths that ship with an add-on or a purchased theme rather than with this
     * repository, so they are only present on some installations.
     *
     * @var array<int, string>
     */
    private const ADD_ON_PATHS = ['Modules/Gateways/', 'resources/themes/'];

    /**
     * @var array<int, string>
     */
    private const BUNDLED_THEMES = ['resources/themes/default/', 'resources/themes/theme_aster/'];

    public function test_application_code_reads_configuration_through_the_config_repository(): void
    {
        $offendingLines = [];

        foreach (self::SCANNED_DIRECTORIES as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(base_path($directory), \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                if ($this->isAddOnPath(path: $file->getPathname())) {
                    continue;
                }

                foreach (file($file->getPathname()) as $number => $line) {
                    if (preg_match('/\benv\(\s*[\'"][A-Z][A-Z0-9_]*[\'"]/', $line)) {
                        $offendingLines[] = $file->getPathname().':'.($number + 1);
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offendingLines,
            'env() must only be called from config/*.php: '.implode(', ', $offendingLines)
        );
    }

    private function isAddOnPath(string $path): bool
    {
        $relativePath = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);

        foreach (self::BUNDLED_THEMES as $bundledTheme) {
            if (str_starts_with($relativePath, $bundledTheme)) {
                return false;
            }
        }

        foreach (self::ADD_ON_PATHS as $addOnPath) {
            if (str_starts_with($relativePath, $addOnPath)) {
                return true;
            }
        }

        return false;
    }

    public function test_installation_values_resolve_from_configuration(): void
    {
        $this->assertTrue(config()->has('app.mode'));
        $this->assertTrue(config()->has('software.purchase_code'));
        $this->assertTrue(config()->has('software.buyer_username'));
        $this->assertTrue(config()->has('software.version'));
        $this->assertTrue(config()->has('software.web_theme'));
        $this->assertTrue(config()->has('software.force_https'));

        $this->assertTrue(
            config('software.development_environment'),
            'A missing DEVELOPMENT_ENVIRONMENT must keep defaulting to true.'
        );
    }

    public function test_synchronizing_config_applies_environment_values_and_drops_the_cached_config_file(): void
    {
        $cachedConfigPath = $this->app->getCachedConfigPath();
        file_put_contents($cachedConfigPath, '<?php return [];');

        EnvironmentManager::synchronizeConfig(values: [
            'APP_MODE' => 'demo',
            'PURCHASE_CODE' => '"a-purchase-code"',
            'FORCE_HTTPS' => 'true',
            'UNMAPPED_KEY' => 'ignored',
        ]);

        $this->assertSame('demo', config('app.mode'));
        $this->assertSame('a-purchase-code', config('software.purchase_code'));
        $this->assertTrue(config('software.force_https'));
        $this->assertFileDoesNotExist($cachedConfigPath);
    }
}
