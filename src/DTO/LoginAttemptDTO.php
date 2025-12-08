<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\DTO;

use DateTimeImmutable;
use JsonSerializable;

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
readonly class LoginAttemptDTO implements JsonSerializable
{
    public function __construct(
        public string $ip,
        public string $username,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
        public ?string $userAgent = null,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function jsonSerialize(): array
    {
        return [
            'ip' => $this->ip,
            'username' => $this->username,
            'occurred_at' => $this->occurredAt->format(DateTimeImmutable::ATOM),
            'user_agent' => $this->userAgent,
        ];
    }
}
