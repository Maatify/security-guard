<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:36
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Enums;

/**
 * 🔒 Built-in security action types used internally by the library.
 * These represent only the core events that the engine understands.
 *
 * Projects may define custom actions using the SecurityAction wrapper.
 */
enum SecurityActionEnum: string
{
    case LOGIN_ATTEMPT = 'login_attempt';
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILURE = 'login_failure';

    case BLOCK_CREATED = 'block_created';
    case BLOCK_REMOVED = 'block_removed';
}
