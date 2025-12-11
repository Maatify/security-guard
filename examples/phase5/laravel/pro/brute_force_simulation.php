<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:28
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 * @note        Phase 5 – PRO Scenario: Brute Force Simulation (Laravel Style, STRICT)
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';

echo "\n=== Laravel PRO – Brute Force Simulation (STRICT) ===\n\n";

// ---------------------------------------------------------------------
// Attach event listener (simple output)
// ---------------------------------------------------------------------
$guard->setEventDispatcher(new class {
    public function dispatch(\Maatify\SecurityGuard\DTO\SecurityEventDTO $e): void
    {
        echo "📡 EVENT: {$e->action->value} | {$e->ip} | {$e->subject}\n";
    }
});

// ---------------------------------------------------------------------
// Configure victim data
// ---------------------------------------------------------------------
$ip = "99.88.77.66";
$subject = "victim@example.com";

echo "➡ Starting brute-force simulation against {$subject}\n\n";

// ---------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------
function brute_force_attempt(
    \Maatify\SecurityGuard\Service\SecurityGuardService $guard,
    string $ip,
    string $subject,
    int $i
): void
{
    // Check early block
    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 BLOCKED EARLY — remaining {$remain}s\n";

        return;
    }

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : "CLI",
        context   : ['attempt' => $i, 'attack_type' => 'brute_force']
    );

    $count = $guard->handleAttempt($attempt, false);

    echo "❌ Attempt {$i} → failed_count={$count}\n";

    if ($guard->isBlocked($ip, $subject)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 USER AUTO-BLOCKED after {$count} failures — {$remain}s remaining\n";
    }

    echo "\n";
}

// ---------------------------------------------------------------------
// Simulate brute-force: 10 attempts
// ---------------------------------------------------------------------

for ($i = 1; $i <= 10; $i++) {
    brute_force_attempt($guard, $ip, $subject, $i);
}

echo "\n=== END BRUTE FORCE SIMULATION ===\n\n";
