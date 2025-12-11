<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example #6
 * MULTI-FLOW SECURITY LOGIC (STRICT)
 *
 * Demonstrates:
 *  - Handling multiple independent login flows simultaneously
 *  - Each flow has its own IP + subject
 *  - Each flow maintains its own counter and blocking logic
 *  - Perfect parity with the Native version but on Slim DI architecture
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim app + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// config
$config = $guard->getConfig();
$window = $config->windowSeconds();

echo "\n=== Slim Example #6 — MULTI FLOW HANDLING (STRICT) ===\n\n";

// =====================================================================
// FLOW #1 — USER A (3 consecutive failures)
// =====================================================================
echo "→ FLOW 1: User A (fail → fail → fail)\n";

$ipA = "192.168.0.10";
$subjectA = "user_A";

for ($i = 1; $i <= 3; $i++) {
    $dtoA = LoginAttemptDTO::now(
        ip        : $ipA,
        subject   : $subjectA,
        resetAfter: $window,
        userAgent : "CLI",
        context   : ['flow' => 'A', 'attempt' => $i]
    );

    $countA = $guard->handleAttempt($dtoA, false);
    echo "User A – Failure {$i} → count = {$countA}\n";
}

echo "\n";

// =====================================================================
// FLOW #2 — USER B (fail → success → fail)
// =====================================================================
echo "→ FLOW 2: User B (fail → success → fail)\n";

$ipB = "192.168.0.50";
$subjectB = "user_B";

// Failure #1
$dtoB1 = LoginAttemptDTO::now(
    ip        : $ipB,
    subject   : $subjectB,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['flow' => 'B', 'attempt' => 'fail_1']
);
$countB1 = $guard->handleAttempt($dtoB1, false);
echo "User B – Failure 1 → count = {$countB1}\n";

// Success (resets)
$dtoBsuccess = LoginAttemptDTO::now(
    ip        : $ipB,
    subject   : $subjectB,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['flow' => 'B', 'attempt' => 'success']
);
$guard->handleAttempt($dtoBsuccess, true);
echo "User B – ✔ Success → counters reset\n";

// Failure after reset (must = 1)
$dtoB2 = LoginAttemptDTO::now(
    ip        : $ipB,
    subject   : $subjectB,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['flow' => 'B', 'attempt' => 'fail_after_reset']
);
$countB2 = $guard->handleAttempt($dtoB2, false);
echo "User B – Failure after reset → count = {$countB2} (expected 1)\n";

echo "\n";

// =====================================================================
// FLOW #3 — USER C (success only)
// =====================================================================
echo "→ FLOW 3: User C (success only)\n";

$ipC = "172.16.10.99";
$subjectC = "user_C";

$dtoCsuccess = LoginAttemptDTO::now(
    ip        : $ipC,
    subject   : $subjectC,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['flow' => 'C', 'action' => 'success']
);

$resultC = $guard->handleAttempt($dtoCsuccess, true);

echo "User C – ✔ success → counters reset. Returned: ";
echo($resultC === null ? "null\n" : "{$resultC}\n");

echo "\n=== END OF MULTI FLOW EXAMPLE ===\n\n";
