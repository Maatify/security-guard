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

use InvalidArgumentException;
use JsonSerializable;

/**
 * 🧩 LoginAttemptDTO
 *
 * Represents a login failure attempt with normalized fields ready for
 * Redis/MySQL/Mongo storage and driver consumption.
 *
 * @package Maatify\SecurityGuard\DTO
 */
readonly class LoginAttemptDTO implements JsonSerializable
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $ip,
        public string $subject,
        public int $occurredAt,
        public int $resetAfter,                 // seconds until the counter resets
        public ?string $userAgent = null,
        public array $context = [],
    ) {
        if (trim($ip) === '') {
            throw new InvalidArgumentException('IP cannot be empty.');
        }

        if (trim($subject) === '') {
            throw new InvalidArgumentException('subject cannot be empty.');
        }

        if ($resetAfter < 0) {
            throw new InvalidArgumentException('resetAfter cannot be negative.');
        }
    }

    /**
     * Factory for an immediate login attempt.
     *
     * @param array<string, mixed> $context
     */
    public static function now(
        string $ip,
        string $subject,
        int $resetAfter,
        ?string $userAgent = null,
        array $context = []
    ): self {
        return new self(
            ip: $ip,
            subject: $subject,
            occurredAt: time(),
            resetAfter: $resetAfter,
            userAgent: $userAgent,
            context: $context
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'ip'          => $this->ip,
            'subject'     => $this->subject,
            'occurred_at' => $this->occurredAt,
            'reset_after' => $this->resetAfter,
            'user_agent'  => $this->userAgent,
            'context'     => $this->context,
        ];
    }
}

