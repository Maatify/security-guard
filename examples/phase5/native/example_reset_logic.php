<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:22
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap.php';

// The subject + IP whose attempts we'll test
$ip = '172.16.0.12';
$subject = 'login';

// Read current config (Phase 5 dynamic flow)
$currentConfig = $guard->getConfig();
$windowSeconds = $currentConfig->windowSeconds();

echo "===== FAILURE → RESET LOGIC =====\n\n";

echo "Window Seconds (resetAfter): {$windowSeconds}\n\n";

// ================================
// ❌ FAILURES (Counter increments)
// ================================

echo "Simulating failures...\n";

for ($i = 1; $i <= 2; $i++) {
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $windowSeconds,
        userAgent : "CLI",
        context   : ['step' => "fail_{$i}"]
    );

    $result = $guard->handleAttempt($attempt, false);

    echo "✖ Failed attempt {$i} → failure count = {$result}\n";
}

// ================================
// ✔ SUCCESS → counter reset
// ================================

echo "\nSimulating successful login...\n";

$successAttempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $windowSeconds,
    userAgent : "CLI",
    context   : ['step' => 'success']
);

// This should reset the counter internally
$guard->handleAttempt($successAttempt, true);

echo "✔ Success → counters reset.\n\n";

echo "Verifying reset...\n";

$checkAttempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $windowSeconds
);

// A fresh failure should now return count = 1
$result = $guard->handleAttempt($checkAttempt, false);

echo "New failure after reset → count = {$result}\n";


