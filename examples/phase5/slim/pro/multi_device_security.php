<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:31
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim PRO Example #5
 * MULTI-DEVICE SECURITY SIMULATION (STRICT)
 *
 * Demonstrates:
 *  - A single user logging in from multiple devices & networks
 *  - Mixed successes and failures per-device
 *  - How Phase 5 sees the full picture and auto-blocks if behavior is suspicious
 *  - ZERO custom logic — STRICT usage of Phase 5 APIs only
 */

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim app + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/../bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// -------------------------------------------------------------
// MULTI-DEVICE CONFIG (STRICT)
// -------------------------------------------------------------
$dto = new SecurityConfigDTO(
    windowSeconds        : 90,
    blockSeconds         : 600,
    maxFailures          : 6,
    identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
    keyPrefix            : "md:",
    backoffEnabled       : true,
    initialBackoffSeconds: 20,
    backoffMultiplier    : 2.0,
    maxBackoffSeconds    : 300
);

$guard->setConfig(new SecurityConfig($dto));

echo "\n=== PRO Example #5 — MULTI DEVICE SECURITY (STRICT) ===\n\n";

// Window TTL
$window = $dto->windowSeconds;

// Victim subject
$subject = "multi_device_user";

// Simulated devices
$devices = [
    [
        'ip'      => '10.0.0.5',
        'ua'      => 'iPhone 15 / Safari',
        'success' => false,
        'context' => ['device' => 'iphone']
    ],
    [
        'ip'      => '10.0.0.6',
        'ua'      => 'MacBook Pro / Chrome',
        'success' => true,
        'context' => ['device' => 'macbook']
    ],
    [
        'ip'      => '192.168.1.22',
        'ua'      => 'Samsung S24 / Chrome',
        'success' => false,
        'context' => ['device' => 'android']
    ],
    [
        'ip'      => '172.16.10.99',
        'ua'      => 'Windows PC / Edge',
        'success' => false,
        'context' => ['device' => 'windows']
    ],
    [
        'ip'      => '185.85.0.88',
        'ua'      => 'VPN / Unknown',
        'success' => false,
        'context' => ['device' => 'vpn']
    ],
];

echo "Simulating 5 different devices for subject: {$subject}\n\n";

// -------------------------------------------------------------
// EXECUTE TRAFFIC
// -------------------------------------------------------------
foreach ($devices as $index => $device) {
    $n = $index + 1;

    echo "→ Device #{$n} ({$device['context']['device']}) — {$device['ip']}\n";

    $attempt = LoginAttemptDTO::now(
        ip        : $device['ip'],
        subject   : $subject,
        resetAfter: $window,
        userAgent : $device['ua'],
        context   : array_merge(
            ['pro' => 'multi_device', 'attempt' => $n],
            $device['context']
        )
    );

    // Strict Phase 5 attempt
    $result = $guard->handleAttempt($attempt, $device['success']);

    // Has the user been blocked due to multi-device suspicious behaviour?
    if ($guard->isBlocked($device['ip'], $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($device['ip'], $subject);

        echo "🚫 ACCOUNT BLOCKED after device #{$n}\n";
        echo "Remaining block time: {$remaining} sec\n\n";
        echo "Phase 5 detected unusual multi-device behavior automatically.\n";
        echo "=== END OF MULTI-DEVICE SECURITY SIMULATION ===\n\n";
        break;
    }

    echo ($device['success'] ? "✔ Success" : "✖ Failure")
         . " → returned = " . var_export($result, true) . "\n\n";
}

