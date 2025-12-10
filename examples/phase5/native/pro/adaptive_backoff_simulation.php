<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:36
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * PRO Example 3:
 * Adaptive backoff simulation (increasing penalty windows).
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';
$config = $guard->getConfig();

$ip = "172.30.15.50";
$subject = "api_login";

echo "\n===== ADAPTIVE BACKOFF SIMULATION =====\n\n";

for ($i = 1; $i <= 6; $i++) {
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $config->windowSeconds(),
        userAgent : "API-CLI",
        context   : ['iteration' => $i]
    );

    $result = $guard->handleAttempt($attempt, false);

    echo "Attempt {$i} → failure count = {$result}\n";

    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 AUTO BLOCKED — remaining: {$remaining} sec\n";
        break;
    }

    // Show adaptive backoff concept
    if ($config->backoffEnabled()) {
        $delay = min(
            $config->initialBackoffSeconds() * ($config->backoffMultiplier() ** ($i - 1)),
            $config->maxBackoffSeconds()
        );

        echo "⏳ Backoff delay suggestion: {$delay} sec\n";
    }
}
