<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:37
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Enums;

/**
 * 🎯 SecurityPlatformEnum
 *
 * Represents where the event originated from.
 * Applications may provide custom platforms using SecurityPlatform value object.
 */
enum SecurityPlatformEnum: string
{
    case WEB    = 'web';
    case API    = 'api';
    case MOBILE = 'mobile';
    case ADMIN  = 'admin';
    case CLI    = 'cli';
    case SYSTEM = 'system';

    public static function isBuiltin(string $value): bool
    {
        return array_any(self::cases(), fn ($case) => $case->value === $value);
    }
}
