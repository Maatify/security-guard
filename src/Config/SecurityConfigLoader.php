<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 01:24
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Config;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;

/**
 * Optional config loader to allow easy integration with ENV or arrays.
 */
final class SecurityConfigLoader
{
    /**
     * Safely cast any mixed value into int with default fallback.
     */
    private static function toInt(mixed $value, int $default): int
    {
        return is_int($value)
            ? $value
            : (is_string($value) && is_numeric($value) ? (int) $value : $default);
    }

    /**
     * Safely cast any mixed value into float with default fallback.
     */
    private static function toFloat(mixed $value, float $default): float
    {
        return is_float($value)
            ? $value
            : (is_string($value) && is_numeric($value) ? (float) $value : $default);
    }

    /**
     * Safely cast any mixed value into string with a default fallback.
     */
    private static function toString(mixed $value, string $default): string
    {
        return is_string($value) ? $value : $default;
    }

    /**
     * Safely cast any mixed value into bool with a default fallback.
     */
    private static function toBool(mixed $value, bool $default): bool
    {
        return is_bool($value)
            ? $value
            : (is_string($value) ? strtolower($value) === 'true' : $default);
    }

    /**
     * Safe enum loader.
     */
    private static function toIdentifierMode(mixed $value): IdentifierModeEnum
    {
        if (is_string($value)) {
            return IdentifierModeEnum::from($value);
        }

        return IdentifierModeEnum::IDENTIFIER_ONLY;
    }

    /**
     * Loads configuration from ENV variables.
     */
    public static function fromEnv(): SecurityConfig
    {
        $dto = new SecurityConfigDTO(
            windowSeconds: self::toInt($_ENV['SG_WINDOW_SECONDS'] ?? null, 900),
            blockSeconds: self::toInt($_ENV['SG_BLOCK_SECONDS'] ?? null, 1800),
            maxFailures: self::toInt($_ENV['SG_MAX_FAILURES'] ?? null, 5),

            identifierMode: self::toIdentifierMode($_ENV['SG_IDENTIFIER_MODE'] ?? null),
            keyPrefix: self::toString($_ENV['SG_KEY_PREFIX'] ?? null, 'sg'),

            backoffEnabled: self::toBool($_ENV['SG_BACKOFF_ENABLED'] ?? null, true),
            initialBackoffSeconds: self::toInt($_ENV['SG_BACKOFF_INITIAL'] ?? null, 60),
            backoffMultiplier: self::toFloat($_ENV['SG_BACKOFF_MULTIPLIER'] ?? null, 3.0),
            maxBackoffSeconds: self::toInt($_ENV['SG_BACKOFF_MAX'] ?? null, 3600),
        );

        return new SecurityConfig($dto);
    }

    /**
     * Loads configuration from a PHP array.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): SecurityConfig
    {
        $dto = new SecurityConfigDTO(
            windowSeconds: self::toInt($config['windowSeconds'] ?? null, 900),
            blockSeconds: self::toInt($config['blockSeconds'] ?? null, 1800),
            maxFailures: self::toInt($config['maxFailures'] ?? null, 5),

            identifierMode: self::toIdentifierMode($config['identifierMode'] ?? null),
            keyPrefix: self::toString($config['keyPrefix'] ?? null, 'sg'),

            backoffEnabled: self::toBool($config['backoffEnabled'] ?? null, true),
            initialBackoffSeconds: self::toInt($config['initialBackoffSeconds'] ?? null, 60),
            backoffMultiplier: self::toFloat($config['backoffMultiplier'] ?? null, 3.0),
            maxBackoffSeconds: self::toInt($config['maxBackoffSeconds'] ?? null, 3600),
        );

        return new SecurityConfig($dto);
    }

    /**
     * Default production-grade config.
     */
    public static function defaults(): SecurityConfig
    {
        return self::fromArray([]);
    }
}

