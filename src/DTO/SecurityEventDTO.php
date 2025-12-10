<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:38
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\DTO;

use JsonSerializable;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;

/**
 * 🧩 SecurityEventDTO
 *
 * A unified, immutable event structure representing any security-related
 * action inside the Security Guard engine.
 *
 * This DTO does NOT replace LoginAttemptDTO or SecurityBlockDTO.
 * Instead, it acts as a normalized, higher-level event envelope that can be
 * used for:
 *  - auditing
 *  - logging
 *  - alerting (Telegram / Webhooks)
 *  - rate-limiter events
 *  - Future SIEM integrations
 *
 * @package Maatify\SecurityGuard\DTO
 */
final readonly class SecurityEventDTO implements JsonSerializable
{
    /**
     * @param   string               $eventId    UUIDv7
     * @param   SecurityAction       $action     Unified action type
     * @param   SecurityPlatform     $platform   Event source (web/mobile/api/admin/custom)
     * @param   int                  $timestamp  UNIX time the event occurred
     * @param   string               $ip         Normalized IP
     * @param   string               $subject    Normalized subject (username/email/device/etc.)
     * @param   int|null             $userId     Optional numeric user ID
     * @param   string|null          $userType   Optional (admin, customer, support…)
     * @param   array<string,mixed>  $context    Additional structured metadata
     */
    public function __construct(
        public string $eventId,
        public SecurityAction $action,
        public SecurityPlatform $platform,
        public int $timestamp,
        public string $ip,
        public string $subject,
        public ?int $userId = null,
        public ?string $userType = null,
        public array $context = [],
    )
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'event_id'  => $this->eventId,
            'action'    => (string)$this->action,
            'platform'  => (string)$this->platform,
            'timestamp' => $this->timestamp,
            'ip'        => $this->ip,
            'subject'   => $this->subject,
            'user_id'   => $this->userId,
            'user_type' => $this->userType,
            'context'   => $this->context,
        ];
    }
}
