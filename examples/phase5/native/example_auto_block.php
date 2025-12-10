<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:18
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap.php';

$ip = '10.10.0.1';
$subject = 'login';

echo "Testing auto-block...\n";

for ($i = 1; $i <= 6; $i++) {
    $attempt = LoginAttemptDTO::now(
        ip        : '192.168.1.10',
        subject   : 'john@example.com',
        resetAfter: 60,                // counter expires after 1 minute
        userAgent : $_SERVER['HTTP_USER_AGENT'] ?? null,
        context   : ['route' => '/login']
    );

    $result = $guard->handleAttempt($attempt, false);

    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 AUTO BLOCKED — Remaining: {$remaining} sec\n";
        break;
    }

    echo "Attempt {$i}: Failure count = {$result}\n";
}
