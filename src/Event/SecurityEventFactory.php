<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:39
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Event;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;

/**
 * 🏭 SecurityEventFactory
 *
 * Converts low-level DTOs (LoginAttemptDTO / SecurityBlockDTO) and raw inputs
 * into unified SecurityEventDTO objects.
 *
 * This layer does NOT affect driver behavior.
 * It is a high-level event normalization layer used for:
 *  - audit logs
 *  - alerting
 *  - webhooks
 *  - rate-limiter bridge
 *  - Future SIEM integrations
 */
final class SecurityEventFactory
{
    /**
     * Generate UUIDv7 for stable chronological ordering.
     */
    private static function uuidV7(): string
    {
        // Simple PHP-only UUIDv7 (sufficient for library use)
        $time = microtime(true);
        $sec  = (int) $time;
        $usec = (int) (($time - $sec) * 1_000_000);

        // 48-bit timestamp
        $timestamp = ($sec << 12) | ($usec >> 8);

        // Set version 7 (0111)
        $timeHigh = ($timestamp & 0xffff000000000000) >> 48;
        $timeLow  = $timestamp & 0x0000ffffffffffff;

        $timeHigh = ($timeHigh & 0x0fff) | 0x7000;

        return sprintf(
            '%04x%012x-%04x-%04x-%012x',
            $timeHigh,
            $timeLow,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffffffffffff)
        );
    }

    // ======================================================================
    //  📌 FROM LoginAttemptDTO
    // ======================================================================

    public static function fromLoginAttempt(
        LoginAttemptDTO $dto,
        SecurityPlatform $platform,
        ?int $userId = null,
        ?string $userType = null
    ): SecurityEventDTO {
        return new SecurityEventDTO(
            eventId: self::uuidV7(),
            action: SecurityAction::fromEnum(SecurityActionEnum::LOGIN_ATTEMPT),
            platform: $platform,
            timestamp: $dto->occurredAt,
            ip: $dto->ip,
            subject: $dto->subject,
            userId: $userId,
            userType: $userType,
            context: [
                'reset_after' => $dto->resetAfter,
                'user_agent'  => $dto->userAgent,
                ...$dto->context,
            ]
        );
    }

    // ======================================================================
    //  📌 FROM SecurityBlockDTO
    // ======================================================================

    public static function blockCreated(
        SecurityBlockDTO $block,
        SecurityPlatform $platform,
        ?int $userId = null,
        ?string $userType = null
    ): SecurityEventDTO {
        return new SecurityEventDTO(
            eventId: self::uuidV7(),
            action: SecurityAction::fromEnum(SecurityActionEnum::BLOCK_CREATED),
            platform: $platform,
            timestamp: $block->createdAt,
            ip: $block->ip,
            subject: $block->subject,
            userId: $userId,
            userType: $userType,
            context: [
                'block_type' => $block->type->value,
                'expires_at' => $block->expiresAt,
            ]
        );
    }

    public static function blockRemoved(
        string $ip,
        string $subject,
        SecurityPlatform $platform,
        ?int $userId = null,
        ?string $userType = null
    ): SecurityEventDTO {
        return new SecurityEventDTO(
            eventId: self::uuidV7(),
            action: SecurityAction::fromEnum(SecurityActionEnum::BLOCK_REMOVED),
            platform: $platform,
            timestamp: time(),
            ip: $ip,
            subject: $subject,
            userId: $userId,
            userType: $userType,
            context: []
        );
    }

    // ======================================================================
    //  📌 CLEANUP event
    // ======================================================================

    public static function cleanup(SecurityPlatform $platform): SecurityEventDTO
    {
        return new SecurityEventDTO(
            eventId: self::uuidV7(),
            action: SecurityAction::custom('cleanup'),
            platform: $platform,
            timestamp: time(),
            ip: 'system',
            subject: 'system',
            userId: null,
            userType: 'system',
            context: []
        );
    }

    // ======================================================================
    //  📌 GENERIC CUSTOM EVENT
    // ======================================================================

    /**
     * Allows the application to generate *any* security event freely.
     *
     * @param array<string,mixed> $context
     */
    public static function custom(
        SecurityAction $action,
        SecurityPlatform $platform,
        string $ip,
        string $subject,
        array $context = [],
        ?int $userId = null,
        ?string $userType = null
    ): SecurityEventDTO {
        return new SecurityEventDTO(
            eventId: self::uuidV7(),
            action: $action,
            platform: $platform,
            timestamp: time(),
            ip: $ip,
            subject: $subject,
            userId: $userId,
            userType: $userType,
            context: $context
        );
    }
}
