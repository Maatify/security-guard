<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 01:52
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;

/**
 * 🔧 AbstractSecurityGuardDriver
 *
 * Provides **shared logic** and helper utilities for all concrete drivers:
 * - RedisSecurityGuard
 * - MySQLSecurityGuard
 * - MongoSecurityGuard
 *
 * Responsibilities:
 * - Normalize subject & IP
 * - Build deterministic identifier via strategy
 * - Provide shared time helpers
 * - Enforce consistent key naming conventions
 * - Decode/encode blocks
 *
 * Concrete drivers implement the actual storage logic only.
 *
 * @package Maatify\SecurityGuard\Drivers
 */
abstract class AbstractSecurityGuardDriver implements SecurityGuardDriverInterface
{
    protected AdapterInterface $adapter;

    protected IdentifierStrategyInterface $identifierStrategy;

    /**
     * @param   AdapterInterface             $adapter
     * @param   IdentifierStrategyInterface  $identifierStrategy
     */
    public function __construct(
        AdapterInterface $adapter,
        IdentifierStrategyInterface $identifierStrategy
    ) {
        $this->adapter = $adapter;
        $this->identifierStrategy = $identifierStrategy;
    }

    // -------------------------------------------------------------------------
    //  🧩 Shared Normalization Helpers
    // -------------------------------------------------------------------------

    protected function normalizeIp(string $ip): string
    {
        return trim(strtolower($ip));
    }

    protected function normalizeSubject(string $subject): string
    {
        return trim(strtolower($subject));
    }

    /**
     * Compute deterministic hashed identifier using the configured strategy.
     *
     * @param   string               $ip
     * @param   string               $subject
     * @param   array<string,mixed>  $context
     *
     * @return string
     */
    protected function makeIdentifier(string $ip, string $subject, array $context = []): string
    {
        return $this->identifierStrategy->makeId(
            $this->normalizeIp($ip),
            $this->normalizeSubject($subject),
            $context
        );
    }

    /**
     * Returns the current UNIX timestamp.
     *
     * @return int
     */
    protected function now(): int
    {
        return time();
    }

    // -------------------------------------------------------------------------
    //  🔐 Block Encoding Helpers
    // -------------------------------------------------------------------------

    /**
     * Convert a SecurityBlockDTO → array for storage.
     *
     * @return array<string, mixed>
     */
    protected function encodeBlock(SecurityBlockDTO $block): array
    {
        return [
            'ip'         => $block->ip,
            'subject'    => $block->subject,
            'type'       => $block->type->value,
            'expires_at' => $block->expiresAt,
            'created_at' => $block->createdAt,
        ];
    }

    /**
     * Convert stored array → SecurityBlockDTO.
     *
     * @param array<string, mixed>|null $row
     *
     * @return SecurityBlockDTO|null
     */
    protected function decodeBlock(?array $row): ?SecurityBlockDTO
    {
        if ($row === null) {
            return null;
        }

        // Validate required fields exist and correct type
        if (
            !isset($row['ip']) ||
            !is_string($row['ip']) ||
            !isset($row['subject']) ||
            !is_string($row['subject']) ||
            !isset($row['type']) ||
            !is_string($row['type']) ||
            !isset($row['expires_at']) ||
            !is_int($row['expires_at']) ||
            !isset($row['created_at']) ||
            !is_int($row['created_at'])
        ) {
            return null; // Or throw an exception if you prefer strict mode
        }

        $enum = BlockTypeEnum::tryFrom($row['type']);
        if ($enum === null) {
            return null; // invalid enum stored
        }

        return new SecurityBlockDTO(
            ip: $row['ip'],
            subject: $row['subject'],
            type: $enum,
            expiresAt: $row['expires_at'],
            createdAt: $row['created_at']
        );
    }

    // -------------------------------------------------------------------------
    //  ⚠️ Abstract Low-Level Methods (implemented by each driver)
    // -------------------------------------------------------------------------

    /**
     * @return int The new failure count.
     */
    abstract protected function doRecordFailure(LoginAttemptDTO $attempt): int;

    /**
     * Completely reset counters for (ip + subject).
     */
    abstract protected function doResetAttempts(string $ip, string $subject): void;

    /**
     * @param   string  $ip
     * @param   string  $subject
     *
     * @return SecurityBlockDTO|null
     */
    abstract protected function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO;

    /**
     * @return int|null Remaining seconds
     */
    abstract protected function doGetRemainingBlockSeconds(string $ip, string $subject): ?int;

    /**
     * Persist block to storage.
     */
    abstract protected function doBlock(SecurityBlockDTO $block): void;

    /**
     * Remove a block.
     */
    abstract protected function doUnblock(string $ip, string $subject): void;

    /**
     * Cleanup expired data.
     */
    abstract protected function doCleanup(): void;

    /**
     * @return array<string, mixed>
     */
    abstract protected function doGetStats(): array;

    // -------------------------------------------------------------------------
    //  🚀 Public Interface Implementation (delegates to abstract methods)
    // -------------------------------------------------------------------------

    public function recordFailure(LoginAttemptDTO $attempt): int
    {
        return $this->doRecordFailure($attempt);
    }

    public function resetAttempts(string $ip, string $subject): void
    {
        $this->doResetAttempts($ip, $subject);
    }

    public function getActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        return $this->doGetActiveBlock($ip, $subject);
    }

    public function isBlocked(string $ip, string $subject): bool
    {
        $block = $this->getActiveBlock($ip, $subject);
        if ($block === null) {
            return false;
        }

        if ($block->expiresAt === 0) {
            return true; // permanent block
        }

        return $block->expiresAt > $this->now();
    }

    public function getRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        return $this->doGetRemainingBlockSeconds($ip, $subject);
    }

    public function block(SecurityBlockDTO $block): void
    {
        $this->doBlock($block);
    }

    public function unblock(string $ip, string $subject): void
    {
        $this->doUnblock($ip, $subject);
    }

    public function cleanup(): void
    {
        $this->doCleanup();
    }

    public function getStats(): array
    {
        return $this->doGetStats();
    }
}
