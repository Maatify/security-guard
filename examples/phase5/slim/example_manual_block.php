<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:08
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example #7
 * MANUAL BLOCK DEMONSTRATION (STRICT)
 *
 * Demonstrates:
 *  - Creating a manual block (admin action)
 *  - Using SecurityBlockDTO with correct structure
 *  - Checking isBlocked() and getRemainingBlockSeconds()
 *  - Using Slim DI container and Phase 5 API only
 */

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// -------------------------------------------------------------
// Prepare environment
// -------------------------------------------------------------
echo "\n=== Slim Example #7 — MANUAL BLOCK (STRICT) ===\n\n";

$ip = "203.0.113.77";
$subject = "manual_block_test";

// Calculate expiration timestamp
$expiresAt = time() + 300; // block for 5 minutes

// -------------------------------------------------------------
// 1) Create a manual block
// -------------------------------------------------------------
$manualBlock = new SecurityBlockDTO(
    ip       : $ip,
    subject  : $subject,
    type     : BlockTypeEnum::MANUAL,
    expiresAt: $expiresAt,
    createdAt: time()
);

$guard->block($manualBlock);

echo "✔ Manual block created for {$ip} / {$subject}\n";

// -------------------------------------------------------------
// 2) Verify the user is now blocked
// -------------------------------------------------------------
if ($guard->isBlocked($ip, $subject)) {
    $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
    echo "🚫 User is blocked. Remaining seconds: {$remaining}\n\n";
} else {
    echo "❌ ERROR: Manual block did not apply.\n\n";
}

// -------------------------------------------------------------
// 3) Try a login attempt → should NOT increment counters
// -------------------------------------------------------------
echo "→ Testing login attempt while blocked...\n";

$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : "CLI",
    context   : ['flow' => 'manual_block_attempt']
);

// success=false but should never increment because user is blocked
$result = $guard->handleAttempt($attempt, false);

echo "handleAttempt() returned: {$result}\n";
echo "Expected: remaining block seconds (NOT a failure count)\n\n";

echo "=== END OF MANUAL BLOCK EXAMPLE ===\n\n";
