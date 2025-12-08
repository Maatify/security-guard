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

namespace Maatify\SecurityGuard\DTO;

use DateTimeImmutable;
use JsonSerializable;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

readonly class SecurityBlockDTO implements JsonSerializable
{
    public function __construct(
        public string $ip,
        public string $reason,
        public DateTimeImmutable $blockedAt,
        public ?DateTimeImmutable $expiresAt = null, // null = permanent block
        public BlockTypeEnum $blockType = BlockTypeEnum::AUTO
    ) {
    }

    /**
     * Returns the remaining block duration in seconds.
     * Null means permanent block.
     */
    public function getRemainingSeconds(): ?int
    {
        if ($this->expiresAt === null) {
            return null;
        }

        return max(0, $this->expiresAt->getTimestamp() - time());
    }

    /**
     * Indicates whether the block is expired.
     */
    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt->getTimestamp() <= time();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'ip' => $this->ip,
            'reason' => $this->reason,
            'blocked_at' => $this->blockedAt->format(DateTimeImmutable::ATOM),
            'expires_at' => $this->expiresAt?->format(DateTimeImmutable::ATOM),
            'block_type' => $this->blockType->value,
            'remaining_seconds' => $this->getRemainingSeconds(),
            'is_expired' => $this->isExpired(),
        ];
    }
}
