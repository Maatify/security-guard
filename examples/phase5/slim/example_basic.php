<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example #1
 * BASIC LOGIN FLOW (STRICT)
 *
 * Matches the Native equivalent but implemented using Slim's DI Container.
 *
 * Demonstrates:
 *  - Failure attempts
 *  - Success → reset counter
 *  - Dynamic resetAfter based on config
 *  - handleAttempt() Phase 5 high-level API
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// Load Slim App + DI wiring
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// Retrieve Phase 5 config
$config = $guard->getConfig();
$window = $config->windowSeconds();

echo "\n=== Slim Example #1 — BASIC LOGIN ===\n\n";

$ip = '127.0.0.50';
$subject = 'basic_login';

// ------------------------------------------------------
// ❌ Failure #1
// ------------------------------------------------------
$attempt1 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : 'CLI'
);

$count1 = $guard->handleAttempt($attempt1, false);
echo "Failure #1 → count = {$count1}\n";

// ------------------------------------------------------
// ❌ Failure #2
// ------------------------------------------------------
$attempt2 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : 'CLI'
);

$count2 = $guard->handleAttempt($attempt2, false);
echo "Failure #2 → count = {$count2}\n";

// ------------------------------------------------------
// ✔ SUCCESS → reset the attempt counter
// ------------------------------------------------------
$attemptSuccess = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : 'CLI'
);

$guard->handleAttempt($attemptSuccess, true);
echo "✔ Success → counters reset.\n";

// ------------------------------------------------------
// ❌ Failure after reset → should return count = 1
// ------------------------------------------------------
$attempt3 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window
);

$count3 = $guard->handleAttempt($attempt3, false);
echo "Failure after reset → count = {$count3}\n\n";
