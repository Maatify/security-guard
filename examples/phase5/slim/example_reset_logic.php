<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:01
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example #3
 * RESET LOGIC DEMONSTRATION (STRICT)
 *
 * This example mirrors the Native reset flow:
 *  - Multiple failures increase the counter.
 *  - A successful attempt resets the failure counter.
 *  - The next failure starts from 1 again.
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// Load Slim + DI container
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// Use the existing configuration
$config = $guard->getConfig();
$window = $config->windowSeconds();

echo "\n=== Slim Example #3 — RESET LOGIC ===\n\n";

$ip = '172.16.0.10';
$subject = 'reset_test_flow';

// --------------------------------------------------------
// STEP 1 — three failures
// --------------------------------------------------------
echo "→ Simulating 3 failed attempts...\n";

for ($i = 1; $i <= 3; $i++) {
    $dto = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $window,
        userAgent : 'CLI',
        context   : ['step' => "fail_{$i}"]
    );

    $count = $guard->handleAttempt($dto, false);

    echo "✖ Failure #{$i} → count = {$count}\n";
}

// --------------------------------------------------------
// STEP 2 — success resets the counter
// --------------------------------------------------------
echo "\n→ Simulating a successful login (expect reset)...\n";

$successAttempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : 'CLI',
    context   : ['step' => 'success_reset']
);

$resetResult = $guard->handleAttempt($successAttempt, true);

echo "✔ Success → counters reset (returned: ";
echo $resetResult === null ? "null)\n" : "{$resetResult})\n";


// --------------------------------------------------------
// STEP 3 — next failure must return count = 1
// --------------------------------------------------------
echo "\n→ Simulating next failure after reset...\n";

$postReset = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $window,
    userAgent : 'CLI',
    context   : ['step' => 'post_reset_fail']
);

$newCount = $guard->handleAttempt($postReset, false);

echo "✖ Failure after reset → count = {$newCount} (expected 1)\n\n";
