<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Contracts;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 14:50:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */
interface SecurityGuardDriverInterface
{
    /**
     * Records a failed login attempt.
     */
    public function recordFailure(LoginAttemptDTO $attempt): void;

    /**
     * Resets failure counters for a given IP or user.
     */
    public function resetAttempts(string $ip, string $username): void;

    /**
     * Checks if the IP or user is currently blocked.
     */
    public function isBlocked(string $ip, string $username): bool;

    /**
     * Retrieves block details if blocked, null otherwise.
     */
    public function getBlockDetails(string $ip, string $username): ?SecurityBlockDTO;

    /**
     * Manually blocks an IP or user.
     */
    public function block(SecurityBlockDTO $block): void;

    /**
     * Manually unblocks an IP or user.
     */
    public function unblock(string $ip, string $username): void;
}
