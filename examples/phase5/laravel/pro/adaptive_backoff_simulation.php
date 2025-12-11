<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:19
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 * @note        Phase 5 – PRO Scenario: Adaptive Backoff Simulation (Laravel Style)
 */

declare(strict_types=1);

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;

$guard = require __DIR__ . '/../bootstrap.php';

echo "\n=== Laravel PRO – Adaptive Backoff Simulation (STRICT) ===\n\n";

$guard->setEventDispatcher(new class {
    public function dispatch(SecurityEventDTO $e): void
    {
        echo "📡 EVENT: {$e->action->value}\n";
        echo "  IP: {$e->ip}\n";
        echo "  Subject: {$e->subject}\n\n";
    }
});

// ---------------------------------------------------------------------
// ENABLE BACKOFF WITHOUT ACCESSING INTERNAL CONFIG
// ---------------------------------------------------------------------
$config = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 60,
        blockSeconds         : 300,
        maxFailures          : 5,
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : "pro:",
        backoffEnabled       : true,
        initialBackoffSeconds: 60,
        backoffMultiplier    : 2.0,
        maxBackoffSeconds    : 3600
    )
);

$guard->setConfig($config);

echo "➡ Starting adaptive backoff simulation...\n\n";

// ---------------------------------------------------------------------
// SIMULATION
// ---------------------------------------------------------------------
$ip = "44.55.66.77";
$subject = "backoff_user";

for ($i = 1; $i <= 7; $i++) {
    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 User is BLOCKED — {$remaining}s remaining\n";
        break;
    }

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $config->windowSeconds(),
        userAgent : 'CLI',
        context   : ['scenario' => 'adaptive_backoff', 'attempt' => $i]
    );

    $count = $guard->handleAttempt($attempt, false);

    echo "❌ Failure {$i} — count={$count}\n";

    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 AUTO-BLOCKED via ADAPTIVE BACKOFF — {$remain}s remaining\n";
        break;
    }

    echo "\n";
}

echo "\n=== END ADAPTIVE BACKOFF SIMULATION ===\n\n";

