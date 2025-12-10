<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:12
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

// ---------------------------------------------------------------------
// 🚫 Simulating failed login attempts
// ---------------------------------------------------------------------

$attempt = LoginAttemptDTO::now(
    ip        : '192.168.1.10',
    subject   : 'john@example.com',
    resetAfter: 60,                // counter expires after 1 minute
    userAgent : $_SERVER['HTTP_USER_AGENT'] ?? null,
    context   : ['route' => '/login']
);

$count = $guard->recordFailure($attempt);

echo "Current Failure Count: {$count}\n";

// ---------------------------------------------------------------------
// 🔒 Check if the subject is now blocked
// ---------------------------------------------------------------------

if ($guard->isBlocked('192.168.1.10', 'john@example.com')) {
    echo "User is blocked!\n";
} else {
    echo "User still allowed.\n";
}
