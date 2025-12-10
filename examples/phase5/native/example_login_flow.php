<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:17
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap.php';

$ip = '127.0.0.1';
$subject = 'login';

function simulate_login(string $ip, string $subject, bool $success, $guard)
{
    $attempt = LoginAttemptDTO::now(
        ip        : '192.168.1.10',
        subject   : 'john@example.com',
        resetAfter: 60,                // counter expires after 1 minute
        userAgent : $_SERVER['HTTP_USER_AGENT'] ?? null,
        context   : ['route' => '/login']
    );

    $result = $guard->handleAttempt($attempt, $success);

    if ($success) {
        echo "✔ Successful login → counters reset\n";
    } else {
        echo "✖ Failed login → failure count = {$result}\n";
    }
}

// Simulate failed attempts
simulate_login($ip, $subject, false, $guard);
simulate_login($ip, $subject, false, $guard);
simulate_login($ip, $subject, false, $guard);

// Now simulate success
simulate_login($ip, $subject, true, $guard);
