<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 14:50:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\DTO;

use JsonSerializable;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityPlatform;


/**
 * 🧩 SecurityBlockDTO
 *
 * Immutable block descriptor used by all drivers (Redis/MySQL/Mongo).
 * Designed for:
 * - fast serialization
 * - cross-driver consistency
 * - safe storage formats
 * - predictable auditing
 *
 * @package Maatify\SecurityGuard\DTO
 */
readonly class SecurityBlockDTO implements JsonSerializable
{
    /**
     * @param string        $ip         Client IP
     * @param string        $subject    Normalized subject (username/email/mobile/UID)
     * @param BlockTypeEnum $type       Type of block (manual, auto, permanent...)
     * @param int           $expiresAt  UNIX timestamp (0 = permanent)
     * @param int           $createdAt  UNIX timestamp
     */
    public function __construct(
        public string $ip,
        public string $subject,
        public BlockTypeEnum $type,
        public int $expiresAt,
        public int $createdAt,
    ) {
    }

    /**
     * Returns the remaining block duration in seconds.
     * Null means permanent.
     *
     * @return int|null
     */
    public function getRemainingSeconds(): ?int
    {
        if ($this->expiresAt === 0) {
            return null; // permanent
        }

        return max(0, $this->expiresAt - time());
    }

    /**
     * Whether the block is expired.
     */
    public function isExpired(): bool
    {
        if ($this->expiresAt === 0) {
            return false; // a permanent block never expires
        }

        return $this->expiresAt <= time();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'ip'                => $this->ip,
            'subject'           => $this->subject,
            'type'              => $this->type->value,
            'expires_at'        => $this->expiresAt,
            'created_at'        => $this->createdAt,
            'remaining_seconds' => $this->getRemainingSeconds(),
            'is_expired'        => $this->isExpired(),
        ];
    }

    /**
     * Convert this block record into a "block created" event.
     */
    public function toCreatedEvent(
        SecurityPlatform $platform,
        ?int $userId = null,
        ?string $userType = null
    ): SecurityEventDTO {
        return SecurityEventFactory::blockCreated(
            block: $this,
            platform: $platform,
            userId: $userId,
            userType: $userType
        );
    }

    /**
     * Convert this block record into a "block removed" event.
     */
    public function toRemovedEvent(
        SecurityPlatform $platform,
        ?int $userId = null,
        ?string $userType = null
    ): SecurityEventDTO {
        return SecurityEventFactory::blockRemoved(
            ip: $this->ip,
            subject: $this->subject,
            platform: $platform,
            userId: $userId,
            userType: $userType
        );
    }

}