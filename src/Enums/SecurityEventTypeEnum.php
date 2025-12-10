<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:58
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Enums;

/**
 * 🎯 SecurityEventTypeEnum
 *
 * Represents the *core* event types emitted by the Security Guard engine.
 * Applications may extend this list by using custom event types in the factory.
 */
enum SecurityEventTypeEnum: string
{
    case LOGIN_FAILURE = 'login_failure';
    case LOGIN_SUCCESS = 'login_success';
    case BLOCK_CREATED = 'block_created';
    case BLOCK_REMOVED = 'block_removed';
    case CLEANUP = 'cleanup';
    case SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    case RATE_LIMIT_VIOLATION = 'rate_limit_violation';

    /**
     * Whether this enum contains the given custom value.
     */
    public static function isBuiltin(string $value): bool
    {
        return array_any(self::cases(), fn ($case) => $case->value === $value);
    }
}
