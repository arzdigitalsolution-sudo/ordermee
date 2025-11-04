<?php

declare(strict_types=1);

namespace ClickCart\Config;

class Env
{
    private static array $cache = [];

    public static function load(string $basePath): void
    {
        $envFile = $basePath . '/.env';
        $exampleFile = $basePath . '/.env.example';

        if (!file_exists($envFile)) {
            if (file_exists($exampleFile)) {
                copy($exampleFile, $envFile);
            } else {
                return;
            }
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            [$name, $value] = array_pad(explode('=', $line, 2), 2, null);
            $name = trim((string)$name);
            $value = trim((string)$value);

            if ($name === '') {
                continue;
            }

            $parsedValue = self::parseValue($value);
            self::$cache[$name] = $parsedValue;
            $_ENV[$name] = $parsedValue;
            putenv("{$name}={$parsedValue}");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$cache[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    private static function parseValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $lower = strtolower($value);
        return match ($lower) {
            'true', '(true)', 'on', 'yes' => true,
            'false', '(false)', 'off', 'no' => false,
            'null', '(null)' => null,
            default => trim($value, "\"'"),
        };
    }
}
