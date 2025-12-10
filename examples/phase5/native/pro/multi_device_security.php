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
 * PRO Example 4:
 * Multi-device login simulation for the same subject.
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';

$subject = "mohamed@example.com";
$config = $guard->getConfig();

$devices = [
    "Chrome on Windows" => "192.168.20.11",
    "Safari on macOS"   => "192.168.20.12",
    "Firefox on Linux"  => "192.168.20.13",
    "Mobile iOS"        => "192.168.20.14",
];

echo "\n===== MULTI DEVICE SECURITY =====\n\n";

foreach ($devices as $device => $ip) {
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $config->windowSeconds(),
        userAgent : $device,
        context   : ['device' => $device]
    );

    $result = $guard->handleAttempt($attempt, false);

    echo "{$device} → failure count = {$result}\n";

    if ($guard->isBlocked($ip, $subject)) {
        echo "🚫 BLOCKED device {$device}\n";
    }
}
