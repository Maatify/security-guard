<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:04
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

// ---------------------------------------------------------------------
// Load Phase 5 Security Guard (Laravel bootstrap style)
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

// ---------------------------------------------------------------------
// Basic Example — Phase 5:
// Demonstrates success → reset and failure → increment
// ---------------------------------------------------------------------

echo "\n=== Laravel Phase 5 — Basic Example (STRICT) ===\n\n";

$ip = '127.0.0.1';
$subject = 'laravel_basic_demo';

// 1) Create DTO for failure attempt
$attempt1 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'basic']
);

$count1 = $guard->handleAttempt($attempt1, false);

echo "❌ Failure #1 → count = {$count1}\n";

// 2) Second failure
$attempt2 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'basic']
);

$count2 = $guard->handleAttempt($attempt2, false);

echo "❌ Failure #2 → count = {$count2}\n";

// 3) Successful login
$attempt3 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'basic']
);

$success = $guard->handleAttempt($attempt3, true);

echo "✅ Success → counters reset (returned: ";
echo ($success === null ? "null" : $success) . ")\n";

// 4) Now verify counter resets by failing again
$attempt4 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['example' => 'basic']
);

$count4 = $guard->handleAttempt($attempt4, false);

echo "❌ Failure after success → count = {$count4}\n";

echo "\n=== END BASIC EXAMPLE ===\n\n";
