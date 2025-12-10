<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:35
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * PRO Example 1:
 * Brute-force simulation for a single subject.
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';

$ip = "192.168.50.10";
$subject = "victim@example.com";

// Load config for window + thresholds
$config = $guard->getConfig();

echo "\n===== BRUTE FORCE SIMULATION =====\n\n";

for ($i = 1; $i <= $config->maxFailures() + 3; $i++) {
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $config->windowSeconds(),
        userAgent : "AttackerBot/1.0",
        context   : ['attempt' => $i]
    );

    $result = $guard->handleAttempt($attempt, false);

    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);

        echo "🚫 BLOCKED at attempt {$i} — remaining = {$remaining} sec\n";
        break;
    }

    echo "✖ Failed attempt {$i} → count = {$result}\n";
}
