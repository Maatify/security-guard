<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:15
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers\MySQL\Contracts;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;

/**
 * 🧩 MySQLDriverInterface
 *
 * Defines the unified SQL-access contract for PDO and DBAL MySQL drivers.
 * SQL logic must be fully encapsulated inside this interface’s implementations.
 *
 * All MySQL-based storage implementations MUST follow the same semantics:
 * - recordFailure: returns new failure count
 * - resetAttempts: clears all counters
 * - getActiveBlock: fetches currently active block or null
 * - block: inserts or updates block entry
 * - unblock: deletes block record
 * - cleanup: removes expired blocks and old attempts
 * - getStats: monitoring dashboard metrics
 */
interface MySQLDriverInterface
{
    /**
     * Record a failed login attempt and return the updated failure count.
     */
    public function doRecordFailure(LoginAttemptDTO $attempt): int;

    /**
     * Reset all login attempt counters for a given subject (user/device) + IP.
     */
    public function doResetAttempts(string $ip, string $subject): void;

    /**
     * Fetch the currently active block for this subject.
     *
     * @param   string  $ip
     * @param   string  $subject
     *
     * @return SecurityBlockDTO|null
     */
    public function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO;

    /**
     * Return the remaining block duration in seconds, or null if permanent or none.
     */
    public function doGetRemainingBlockSeconds(string $ip, string $subject): ?int;

    /**
     * Write or update a block entry.
     */
    public function doBlock(SecurityBlockDTO $block): void;

    /**
     * Remove block and attempts for this subject.
     */
    public function doUnblock(string $ip, string $subject): void;

    /**
     * Clean up expired blocks and old attempts based on TTL.
     */
    public function doCleanup(): void;

    /**
     * Statistics for monitoring / dashboard purposes.
     *
     * @return array<string,mixed>
     */
    public function doGetStats(): array;
}

