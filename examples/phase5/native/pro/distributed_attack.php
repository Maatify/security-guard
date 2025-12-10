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
 * PRO Example 2:
 * Distributed brute-force attack (multiple IPs attacking the same subject).
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';

$subject = "shared_account";
$config = $guard->getConfig();

echo "\n===== DISTRIBUTED ATTACK SIMULATION =====\n\n";

$attackers = [
    "10.0.0.1",
    "10.0.0.2",
    "10.0.0.3",
    "10.0.0.4",
    "10.0.0.5",
    "10.0.0.6",
    "10.0.0.7",
    "10.0.0.8",
    "10.0.0.9",
    "10.0.0.10",
];

foreach ($attackers as $i => $ip) {
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $config->windowSeconds(),
        userAgent : "DistributedBot/2.0",
        context   : ['ip_index' => $i + 1]
    );

    $result = $guard->handleAttempt($attempt, false);

    echo "IP {$ip} → attempt → failure count = {$result}\n";

    if ($guard->isBlocked($ip, $subject)) {
        echo "🚫 BLOCKED IP {$ip}\n";
    }
}
