<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:32
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 * @note        Phase 5 – PRO Scenario: Multi-Device Security Simulation (Laravel Style, STRICT)
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';

echo "\n=== Laravel PRO – Multi-Device Security Simulation (STRICT) ===\n\n";

// ---------------------------------------------------------------------
// Attach event listener for demonstration
// ---------------------------------------------------------------------
$guard->setEventDispatcher(new class {
    public function dispatch(\Maatify\SecurityGuard\DTO\SecurityEventDTO $e): void
    {
        echo "📡 EVENT: {$e->action->value} | {$e->ip} | {$e->subject}\n";
        if (! empty($e->context)) {
            echo "  Context: " . json_encode($e->context) . "\n";
        }
    }
});

// ---------------------------------------------------------------------
// DEVICES attempting to log into same subject
// ---------------------------------------------------------------------
$subject = "same_user";
$devices = [
    [
        'ip'     => '21.21.21.21',
        'agent'  => 'Mozilla/5.0 Desktop',
        'device' => 'desktop'
    ],
    [
        'ip'     => '99.99.99.99',
        'agent'  => 'Mobile Safari iOS',
        'device' => 'mobile'
    ],
    [
        'ip'     => '55.44.33.22',
        'agent'  => 'Android Tablet',
        'device' => 'tablet'
    ],
    [
        'ip'     => '199.199.199.199',
        'agent'  => 'UnknownSignature',
        'device' => 'unknown'
    ]
];

echo "➡ Multiple devices attempting to access subject={$subject}\n\n";

// ---------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------
function device_attempt(
    \Maatify\SecurityGuard\Service\SecurityGuardService $guard,
    string $ip,
    string $subject,
    string $agent,
    string $deviceType,
    int $i
): void
{
    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 BLOCKED for IP {$ip} — {$remain}s remaining\n";

        return;
    }

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : $agent,
        context   : [
            'device'  => $deviceType,
            'attempt' => $i,
            'flow'    => 'multi_device_security'
        ]
    );

    // Simulate failures for all devices
    $count = $guard->handleAttempt($attempt, false);

    echo "❌ Device {$deviceType} ({$ip}) → attempt={$i}, count={$count}\n";

    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 AUTO-BLOCKED triggered — {$remain}s remaining\n";
    }

    echo "\n";
}

// ---------------------------------------------------------------------
// RUN MULTI-DEVICE SIMULATION
// Each device tries twice
// ---------------------------------------------------------------------
$attemptCounter = 1;

foreach ($devices as $device) {
    for ($i = 1; $i <= 2; $i++) {
        // Early stop if user is blocked globally
        if ($guard->isBlocked($device['ip'], $subject)) {
            echo "🚫 BLOCK: Further attempts skipped for IP {$device['ip']}\n";
            break;
        }

        device_attempt(
            guard     : $guard,
            ip        : $device['ip'],
            subject   : $subject,
            agent     : $device['agent'],
            deviceType: $device['device'],
            i         : $attemptCounter
        );

        $attemptCounter++;

        // Once ANY device causes block → scenario stops
        if ($guard->isBlocked($device['ip'], $subject)) {
            echo "🚫 User {$subject} is BLOCKED — stopping multi-device simulation.\n";
            break 2;
        }
    }
}

echo "\n=== END MULTI-DEVICE SECURITY SIMULATION ===\n\n";
