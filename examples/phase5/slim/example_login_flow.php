<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:06
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example #5
 * COMPLETE LOGIN FLOW (STRICT)
 *
 * Demonstrates:
 *  - Multiple failures
 *  - Success → resets counters
 *  - New failure after reset = count 1
 *  - Slim DI + SecurityGuardService::handleAttempt()
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim + DI
// -------------------------------------------------------------
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// Load effective config
$config = $guard->getConfig();
$window = $config->windowSeconds();

echo "\n=== Slim Example #5 — LOGIN FLOW (STRICT) ===\n\n";

$ip      = "172.20.10.50";
$subject = "login_flow_user";

// -------------------------------------------------------------
// STEP 1 — FAILURE #1
// -------------------------------------------------------------
$fail1 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['step' => 'fail_1']
);

$count1 = $guard->handleAttempt($fail1, false);
echo "✖ Failure #1 → count = {$count1}\n";


// -------------------------------------------------------------
// STEP 2 — FAILURE #2
// -------------------------------------------------------------
$fail2 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['step' => 'fail_2']
);

$count2 = $guard->handleAttempt($fail2, false);
echo "✖ Failure #2 → count = {$count2}\n";


// -------------------------------------------------------------
// STEP 3 — SUCCESS → resets attempts
// -------------------------------------------------------------
$success = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['step' => 'success']
);

$resetResult = $guard->handleAttempt($success, true);

echo "✔ Successful login → counters reset. Returned: ";
echo ($resetResult === null ? "null\n" : "{$resetResult}\n");


// -------------------------------------------------------------
// STEP 4 — FAILURE after reset (must be = 1)
// -------------------------------------------------------------
$postResetFailure = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : "CLI",
    context   : ['step' => 'post_reset_fail']
);

$count3 = $guard->handleAttempt($postResetFailure, false);

echo "✖ Failure after reset → count = {$count3} (expected 1)\n\n";

echo "=== END OF LOGIN FLOW EXAMPLE ===\n\n";
