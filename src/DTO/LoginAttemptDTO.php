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
use InvalidArgumentException;
use JsonSerializable;

readonly class LoginAttemptDTO implements JsonSerializable
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $ip,
        public string $username,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
        public ?string $userAgent = null,
        public array $context = [],
    ) {
        if (trim($ip) === '') {
            throw new InvalidArgumentException('IP cannot be empty.');
        }

        if (trim($username) === '') {
            throw new InvalidArgumentException('Username cannot be empty.');
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function now(
        string $ip,
        string $username,
        ?string $userAgent = null,
        array $context = []
    ): self {
        return new self(
            $ip,
            $username,
            new DateTimeImmutable(),
            $userAgent,
            $context
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'ip' => $this->ip,
            'username' => $this->username,
            'occurred_at' => $this->occurredAt->format(DateTimeImmutable::ATOM),
            'user_agent' => $this->userAgent,
            'context' => $this->context,
        ];
    }
}
