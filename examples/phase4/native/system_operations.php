<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:57
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

// ---------------------------------------------------------
// 🔥 Simulate failed attempts to generate some activity
// ---------------------------------------------------------
$attempt = LoginAttemptDTO::now(
    ip        : '10.0.0.20',
    subject   : 'system-test@example.com',
    resetAfter: 60
);

$guard->recordFailure($attempt);
$guard->recordFailure($attempt);

echo "Two failures recorded.\n";

// ---------------------------------------------------------
// 📊 Get security stats (driver-provided)
// ---------------------------------------------------------
$stats = $guard->getStats();
echo "Current Stats:\n";
print_r($stats);

// ---------------------------------------------------------
// 🔄 Reset attempts (simulate successful login)
// ---------------------------------------------------------
$guard->resetAttempts('10.0.0.20', 'system-test@example.com');
echo "Attempts reset.\n";

// ---------------------------------------------------------
// 🧹 Run cleanup (maintenance event)
// ---------------------------------------------------------
$guard->cleanup();
echo "Cleanup executed.\n";
