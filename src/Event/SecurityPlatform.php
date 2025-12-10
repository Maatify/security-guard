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

namespace Maatify\SecurityGuard\Event;

use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;

/**
 * 🌐 Extensible Platform Wrapper
 *
 * Supports both built-in enums and project-defined platforms.
 */
final class SecurityPlatform
{
    public function __construct(
        public readonly string $value
    ) {
    }

    public static function fromEnum(SecurityPlatformEnum $enum): self
    {
        return new self($enum->value);
    }

    public static function custom(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
