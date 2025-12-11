<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:10
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

// ---------------------------------------------------------------------
// Load Security Guard (Laravel bootstrap style)
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — LOGIN FLOW Example (STRICT) ===\n\n";

$ip = '172.20.10.10';
$subject = 'login_flow_demo';

// Helper to print clear messages
function print_step(string $label, mixed $value): void
{
    echo "{$label}: " . ($value === null ? 'null' : $value) . "\n";
}

// ---------------------------------------------------------------------
// STEP 1 — First failure
// ---------------------------------------------------------------------
$step1 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['flow' => 'login', 'step' => 1]
);

$fail1 = $guard->handleAttempt($step1, false);
print_step("❌ Failure #1 → count", $fail1);

// ---------------------------------------------------------------------
// STEP 2 — Second failure
// ---------------------------------------------------------------------
$step2 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['flow' => 'login', 'step' => 2]
);

$fail2 = $guard->handleAttempt($step2, false);
print_step("❌ Failure #2 → count", $fail2);

// ---------------------------------------------------------------------
// STEP 3 — Successful login → counter resets
// ---------------------------------------------------------------------
$step3 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['flow' => 'login', 'step' => 'success']
);

$success = $guard->handleAttempt($step3, true);
print_step("✅ Success → counter reset (returned)", $success);

// ---------------------------------------------------------------------
// STEP 4 — Failure AFTER success → count should be = 1
// ---------------------------------------------------------------------
$step4 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['flow' => 'login', 'step' => 'after_reset']
);

$failAgain = $guard->handleAttempt($step4, false);
print_step("❌ Failure after success → count", $failAgain);

echo "\n=== END LOGIN FLOW EXAMPLE ===\n\n";
