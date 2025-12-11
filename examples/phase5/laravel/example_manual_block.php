<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:13
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

// ---------------------------------------------------------------------
// Load Security Guard
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — MANUAL BLOCK Example (STRICT) ===\n\n";

$ip = '203.0.113.22';
$subject = 'manual_block_user';

// ---------------------------------------------------------------------
// STEP 1 — Create a Manual Block (Admin Action)
// ---------------------------------------------------------------------
$expires = time() + 600; // block for 10 minutes

$block = new SecurityBlockDTO(
    ip       : $ip,
    subject  : $subject,
    type     : BlockTypeEnum::MANUAL,
    expiresAt: $expires,
    createdAt: time()
);

// Manually block the user
$guard->block($block);

echo "🚫 Manual block created for {$subject} ({$ip})\n";
echo "Expires at: {$expires}\n\n";

// ---------------------------------------------------------------------
// STEP 2 — Verify that user is blocked BEFORE any attempt
// ---------------------------------------------------------------------
if ($guard->isBlocked($ip, $subject)) {
    $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
    echo "User is currently BLOCKED → remaining: {$remaining} seconds\n\n";
}

// ---------------------------------------------------------------------
// STEP 3 — Try a login attempt (should not increment counters)
// ---------------------------------------------------------------------
$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : "CLI",
    context   : ['step' => 'blocked_attempt']
);

// This should return remaining block seconds (NOT a count)
$result = $guard->handleAttempt($attempt, false);

echo "Attempt while blocked → returned: {$result} (remaining seconds)\n\n";

// ---------------------------------------------------------------------
// STEP 4 — Unblock Manually
// ---------------------------------------------------------------------
$guard->unblock($ip, $subject);

echo "🟢 User manually unblocked.\n\n";

// ---------------------------------------------------------------------
// STEP 5 — Try again (should act as normal failure = count=1)
// ---------------------------------------------------------------------
$attempt2 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : "CLI",
    context   : ['step' => 'after_unblock']
);

$count = $guard->handleAttempt($attempt2, false);

echo "❌ Failure after unblock → count = {$count}\n";

echo "\n=== END MANUAL BLOCK EXAMPLE ===\n\n";
