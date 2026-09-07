<?php

namespace App\Utils;

use Illuminate\Support\Facades\Config;

/**
 * Single entry point for writing .env values.
 *
 * Application code reads these values through the config repository, never
 * through env(), so `php artisan config:cache` stays safe. Whatever writes to
 * .env therefore has to keep the config repository in step: the runtime config
 * for the current request, and the cached config file for the next one.
 */
class EnvironmentManager
{
    /**
     * .env keys that back a config entry.
     *
     * @var array<string, string>
     */
    private const CONFIG_KEYS = [
        'APP_NAME' => 'app.name',
        'APP_ENV' => 'app.env',
        'APP_DEBUG' => 'app.debug',
        'APP_URL' => 'app.url',
        'APP_MODE' => 'app.mode',
        'PURCHASE_CODE' => 'software.purchase_code',
        'BUYER_USERNAME' => 'software.buyer_username',
        'ADMIN_NAME' => 'software.admin_name',
        'ADMIN_IDENTIFIER' => 'software.admin_identifier',
        'SOFTWARE_VERSION' => 'software.version',
        'DEVELOPMENT_ENVIRONMENT' => 'software.development_environment',
        'WEB_THEME' => 'software.web_theme',
        'FORCE_HTTPS' => 'software.force_https',
    ];

    /**
     * .env keys whose value is a boolean flag.
     *
     * @var array<int, string>
     */
    private const BOOLEAN_KEYS = [
        'APP_DEBUG',
        'DEVELOPMENT_ENVIRONMENT',
        'FORCE_HTTPS',
    ];

    /**
     * Write a single value to .env and keep the config repository in step.
     */
    public static function setValue(string $envKey, mixed $envValue): string
    {
        $formattedValue = self::writeToEnvironmentFile(values: [$envKey => $envValue]);
        self::synchronizeConfig(values: [$envKey => $envValue]);

        return $formattedValue[$envKey];
    }

    /**
     * Write several values to .env in one pass and keep the config repository in step.
     *
     * @param  array<string, mixed>  $values
     */
    public static function setValues(array $values): void
    {
        self::writeToEnvironmentFile(values: $values);
        self::synchronizeConfig(values: $values);
    }

    /**
     * Apply .env values to the config repository and discard the cached config file.
     *
     * Call this after writing .env by any other means, so the current request
     * sees the new values and the next request rebuilds its cache.
     *
     * @param  array<string, mixed>  $values
     */
    public static function synchronizeConfig(array $values): void
    {
        foreach ($values as $envKey => $envValue) {
            $configKey = self::CONFIG_KEYS[$envKey] ?? null;
            if ($configKey === null) {
                continue;
            }

            Config::set($configKey, self::castValue(envKey: $envKey, envValue: $envValue));
        }

        self::forgetCachedConfig();
    }

    /**
     * Remove bootstrap/cache/config.php so the next request reads .env again.
     */
    public static function forgetCachedConfig(): void
    {
        $cachedConfigPath = app()->getCachedConfigPath();

        if (is_file($cachedConfigPath)) {
            @unlink($cachedConfigPath);
        }
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string> the value as it was written, per key
     */
    private static function writeToEnvironmentFile(array $values): array
    {
        $envFile = app()->environmentFilePath();
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        $formattedValues = [];
        foreach ($values as $envKey => $envValue) {
            $formattedValues[$envKey] = self::formatValue(envValue: $envValue);
        }

        $remainingKeys = $formattedValues;
        $newLines = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $matchedKey = null;
            foreach ($formattedValues as $envKey => $formattedValue) {
                if (preg_match("/^{$envKey}\s*=/i", trim($line))) {
                    $matchedKey = $envKey;
                    break;
                }
            }

            if ($matchedKey !== null) {
                $newLines[] = "{$matchedKey}={$formattedValues[$matchedKey]}";
                unset($remainingKeys[$matchedKey]);
            } else {
                $newLines[] = $line;
            }
        }

        foreach ($remainingKeys as $envKey => $formattedValue) {
            $newLines[] = "{$envKey}={$formattedValue}";
        }

        file_put_contents($envFile, implode(PHP_EOL, $newLines).PHP_EOL);

        return $formattedValues;
    }

    /**
     * Booleans are written as true/false: a plain string cast of false would
     * write an empty value to .env.
     */
    private static function formatValue(mixed $envValue): string
    {
        if (is_bool($envValue)) {
            return $envValue ? 'true' : 'false';
        }

        return preg_match('/[^a-zA-Z0-9]/', (string) $envValue)
            ? '"'.$envValue.'"'
            : (string) $envValue;
    }

    private static function castValue(string $envKey, mixed $envValue): mixed
    {
        if (in_array($envKey, self::BOOLEAN_KEYS, true)) {
            return filter_var($envValue, FILTER_VALIDATE_BOOLEAN);
        }

        return is_string($envValue) ? trim($envValue, '"') : $envValue;
    }
}
