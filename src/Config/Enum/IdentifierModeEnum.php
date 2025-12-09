<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 01:22
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Config\Enum;

/**
 * IdentifierModeEnum defines how Security Guard builds the unique identifier
 * for tracking failures and applying blocking rules.
 *
 * @enum
 */
enum IdentifierModeEnum: string
{
    case IDENTIFIER_ONLY = 'identifier_only';        // email / username / userId only
    case IDENTIFIER_AND_IP = 'identifier_and_ip';    // identifier + IP combined
    case IP_ONLY = 'ip_only';                        // IP-based throttling only
}
