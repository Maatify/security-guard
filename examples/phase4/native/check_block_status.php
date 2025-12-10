<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:57
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

// ---------------------------------------------------------
// 🛑 Simulate multiple failed attempts
// ---------------------------------------------------------

$ip = '203.0.113.77';
$subject = 'block-check@example.com';

$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: 60
);

for ($i = 1; $i <= 5; $i++) {
    $count = $guard->recordFailure($attempt);
    echo "Failure {$i} — count={$count}\n";
}

// ---------------------------------------------------------
// 🔐 Check if blocked
// ---------------------------------------------------------

if ($guard->isBlocked($ip, $subject)) {
    echo "User is BLOCKED.\n";

    $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
    echo "Remaining block time: {$remaining} seconds\n";
} else {
    echo "User is NOT blocked.\n";
}
