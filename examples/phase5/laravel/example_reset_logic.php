<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:08
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

// ---------------------------------------------------------------------
// Load SecurityGuardService (Laravel bootstrap style)
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — RESET LOGIC Example (STRICT) ===\n\n";

$ip = '192.168.100.55';
$subject = 'reset_logic_demo';

// ---------------------------------------------------------------------
// #1 — First Failure
// ---------------------------------------------------------------------
$fail1 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'reset_logic', 'step' => 1]
);

$count1 = $guard->handleAttempt($fail1, false);

echo "❌ Failure #1 → count = {$count1}\n";

// ---------------------------------------------------------------------
// #2 — Second Failure
// ---------------------------------------------------------------------
$fail2 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'reset_logic', 'step' => 2]
);

$count2 = $guard->handleAttempt($fail2, false);

echo "❌ Failure #2 → count = {$count2}\n";

// ---------------------------------------------------------------------
// #3 — Now simulate SUCCESS → counter should reset to 0
// ---------------------------------------------------------------------
$successAttempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'reset_logic', 'step' => 'success']
);

$success = $guard->handleAttempt($successAttempt, true);

echo "✅ Success → counter reset (returned = ";
echo($success === null ? "null" : $success);
echo ")\n";

// ---------------------------------------------------------------------
// #4 — Another failure NOW should return count = 1 again
// ---------------------------------------------------------------------
$failAfterReset = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'reset_logic', 'step' => 'after_success']
);

$countAfterReset = $guard->handleAttempt($failAfterReset, false);

echo "❌ Failure after success → count = {$countAfterReset}\n";

echo "\n=== END RESET LOGIC EXAMPLE ===\n\n";
