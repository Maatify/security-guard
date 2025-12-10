<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:36
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Event;

use Maatify\SecurityGuard\Enums\SecurityActionEnum;

/**
 * 🌐 Extensible Action Wrapper
 *
 * Allows projects to define custom security actions while still supporting
 * built-in enum values.
 */
final readonly class SecurityAction
{
    public function __construct(
        public string $value
    ) {
    }

    public static function fromEnum(SecurityActionEnum $enum): self
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
