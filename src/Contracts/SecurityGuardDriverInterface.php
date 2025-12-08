<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-07 23:17:05
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\SecurityGuard\Contracts;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;

interface SecurityGuardDriverInterface
{
    public function recordLoginAttempt(LoginAttemptDTO $attempt): void;

    public function getFailedAttempts(string $username, string $ipAddress): int;

    public function blockIp(string $ipAddress, string $reason, ?int $durationInSeconds = null): void;

    public function unblockIp(string $ipAddress): void;

    public function isIpBlocked(string $ipAddress): ?SecurityBlockDTO;

    public function resetFailedAttempts(string $username, string $ipAddress): void;
}
