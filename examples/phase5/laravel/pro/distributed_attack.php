<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:30
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 * @note        Phase 5 – PRO Scenario: Distributed Attack Simulation (Laravel Style, STRICT)
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';

echo "\n=== Laravel PRO – Distributed Attack Simulation (STRICT) ===\n\n";

// ---------------------------------------------------------------------
// Attach event listener
// ---------------------------------------------------------------------
$guard->setEventDispatcher(new class {
    public function dispatch(\Maatify\SecurityGuard\DTO\SecurityEventDTO $e): void
    {
        echo "📡 EVENT: {$e->action->value} | IP={$e->ip} | Subject={$e->subject}\n";
    }
});

// ---------------------------------------------------------------------
// Scenario setup: multiple IPs attacking same account
// ---------------------------------------------------------------------
$targetSubject = "target@user.com";

// A large botnet of attacking IPs
$botnet = [
    "10.10.1.1",
    "10.10.1.2",
    "10.10.1.3",
    "10.10.1.4",
    "10.10.1.5",
    "10.10.1.6",
    "10.10.1.7",
    "10.10.1.8",
    "10.10.1.9",
    "10.10.1.10",
    "10.10.1.11",
    "10.10.1.12"
];

echo "➡ Botnet attacking subject: {$targetSubject}\n\n";

// ---------------------------------------------------------------------
// Helper function: simulate attack from single IP
// ---------------------------------------------------------------------
function simulate_distributed_attempt(
    \Maatify\SecurityGuard\Service\SecurityGuardService $guard,
    string $ip,
    string $subject,
    int $i
): void
{
    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 BLOCKED EARLY: IP {$ip} — {$remain}s remaining\n";

        return;
    }

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : "CLI",
        context   : [
            'flow'       => 'distributed_attack',
            'attempt_no' => $i,
            'botnet_ip'  => $ip
        ]
    );

    $count = $guard->handleAttempt($attempt, false);

    echo "❌ Attempt #{$i} from IP {$ip} → failure_count={$count}\n";

    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 Subject AUTO-BLOCKED via distributed attack logic — {$remain}s remaining\n";
    }

    echo "\n";
}

// ---------------------------------------------------------------------
// Run simulation
// Each IP tries once — this often triggers early block
// ---------------------------------------------------------------------
$attemptNumber = 1;

foreach ($botnet as $ip) {
    simulate_distributed_attempt($guard, $ip, $targetSubject, $attemptNumber);

    // Once the target is blocked, no need to continue
    if ($guard->isBlocked($ip, $targetSubject)) {
        echo "🚫 TARGET BLOCKED — stopping botnet simulation.\n";
        break;
    }

    $attemptNumber++;
}

echo "\n=== END DISTRIBUTED ATTACK SIMULATION ===\n\n";
