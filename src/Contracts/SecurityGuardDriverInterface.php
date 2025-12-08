<?php

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

declare(strict_types=1);

namespace Maatify\SecurityGuard\Contracts;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;

interface SecurityGuardDriverInterface
{
    /**
     * Records a failed login attempt and returns the current failure count.
     */
    public function recordFailure(LoginAttemptDTO $attempt): int;

    /**
     * Resets failure counters for a given IP and username.
     */
    public function resetAttempts(string $ip, string $username): void;

    /**
     * Returns the active block if it exists.
     */
    public function getActiveBlock(string $ip, string $username): ?SecurityBlockDTO;

    /**
     * Quick boolean check wrapper.
     */
    public function isBlocked(string $ip, string $username): bool;

    /**
     * Returns the remaining block duration in seconds.
     */
    public function getRemainingBlockSeconds(string $ip, string $username): ?int;

    /**
     * Manually blocks an IP or user.
     */
    public function block(SecurityBlockDTO $block): void;

    /**
     * Manually unblocks an IP or user.
     */
    public function unblock(string $ip, string $username): void;

    /**
     * Cleanup expired blocks and old login attempts.
     */
    public function cleanup(): void;

    /**
     * Get statistics for monitoring and dashboards.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array;
}
