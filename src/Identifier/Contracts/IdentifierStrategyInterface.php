<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 01:50
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);


namespace Maatify\SecurityGuard\Identifier\Contracts;

/**
 * 🧩 IdentifierStrategyInterface
 *
 * Defines the contract for generating a **deterministic, secure, and storage-agnostic**
 * identifier for tracking login failures and security blocks.
 *
 * This abstraction allows the system to:
 * - Swap identifier logic without touching drivers
 * - Support custom hashing, composite keys, device-based keys, etc.
 * - Enable consistent identifiers across Redis, MySQL, and MongoDB
 * - Remain fully configurable and future-proof
 *
 * Typical strategies:
 * - `sha1("$ip|$subject")`
 * - `sha1($subject)` (subject-only mode)
 * - `sha1("$ip|$subject|$context['user_agent']")`
 * - enterprise composite (tenant_id + subject)
 *
 * @package Maatify\SecurityGuard\Identifier\Contracts
 */
interface IdentifierStrategyInterface
{
    /**
     * Generate a normalized hashed identifier for a login subject + IP combination.
     *
     * @param string              $ip      The client IP address.
     * @param string              $subject The authenticated entity (username/email/phone/etc.).
     * @param array<string,mixed> $context Additional contextual data (device_id, user_agent, tenant_id...).
     *
     * @return string A deterministic, hashed, storage-safe identifier.
     */
    public function makeId(
        string $ip,
        string $subject,
        array $context = []
    ): string;
}