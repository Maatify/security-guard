<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

// ---------------------------------------------------------------------
// Load Phase 5 Guard (Laravel bootstrap style)
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

// ---------------------------------------------------------------------
// AUTO-BLOCK EXAMPLE (STRICT)
// Demonstrates how the guard automatically blocks a subject
// once maxFailures() threshold is reached.
// ---------------------------------------------------------------------

echo "\n=== Laravel Phase 5 — AUTO BLOCK Example (STRICT) ===\n\n";

$ip = '10.10.10.5';
$subject = 'laravel_auto_block';

// Max failures threshold:
$limit = $guard->getConfig()->maxFailures();

echo "Config maxFailures = {$limit}\n\n";

for ($i = 1; $i <= ($limit + 1); $i++) {
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : 'CLI',
        context   : ['example' => 'auto_block', 'attempt' => $i]
    );

    $result = $guard->handleAttempt($attempt, false);

    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);

        echo "🚫 BLOCKED on attempt {$i}\n";
        echo "Remaining block time: {$remaining} seconds\n";

        echo "\n=== END AUTO BLOCK EXAMPLE ===\n\n";
        exit;
    }

    echo "❌ Failure #{$i} → count returned = {$result}\n";
}

echo "\n⚠ Finished loop without blocking (unexpected).\n";
echo "=== END AUTO BLOCK EXAMPLE ===\n\n";
