<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:58
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

$ip = '198.51.100.44';
$subject = 'flow@example.com';

echo "========== FULL SECURITY FLOW ==========\n";

// ---------------------------------------------------------
// 1) Record multiple failures
// ---------------------------------------------------------
$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: 60
);

echo "• Recording failures...\n";
for ($i = 1; $i <= 3; $i++) {
    $count = $guard->recordFailure($attempt);
    echo "  → Failure {$i}: count={$count}\n";
}

// ---------------------------------------------------------
// 2) Check if blocked
// ---------------------------------------------------------
echo "• Checking block status...\n";

if ($guard->isBlocked($ip, $subject)) {
    echo "  → User BLOCKED\n";
} else {
    echo "  → User NOT blocked\n";
}

// ---------------------------------------------------------
// 3) Manually block user
// ---------------------------------------------------------
echo "• Creating manual block...\n";

$manualBlock = new SecurityBlockDTO(
    ip       : $ip,
    subject  : $subject,
    type     : BlockTypeEnum::MANUAL,
    expiresAt: time() + 120,  // 2 minutes
    createdAt: time()
);

$guard->block($manualBlock);
echo "  → Manual block applied.\n";

// ---------------------------------------------------------
// 4) Remaining block time
// ---------------------------------------------------------
$remaining = $guard->getRemainingBlockSeconds($ip, $subject);
echo "• Remaining block time: {$remaining} seconds\n";

// ---------------------------------------------------------
// 5) Remove block
// ---------------------------------------------------------
$guard->unblock($ip, $subject);
echo "• Block removed.\n";

// ---------------------------------------------------------
// 6) Reset attempts
// ---------------------------------------------------------
$guard->resetAttempts($ip, $subject);
echo "• Attempts reset.\n";

// ---------------------------------------------------------
// 7) Cleanup
// ---------------------------------------------------------
$guard->cleanup();
echo "• Cleanup executed.\n";

echo "========== END FLOW ==========\n";
