<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-07 23:16:42
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\SecurityGuard\DTO;

use DateTimeImmutable;

final class SecurityBlockDTO
{
    public function __construct(
        public readonly string $ipAddress,
        public readonly string $reason,
        public readonly ?DateTimeImmutable $expiresAt,
    ) {
    }
}
