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
 * Phase 5 – Slim Example #2
 * AUTO BLOCK DEMONSTRATION (STRICT)
 *
 * Matches the Native auto-block example but implemented for Slim.
 *
 * Demonstrates:
 *  - Consecutive failures
 *  - Automatic blocking when threshold exceeded
 *  - Querying remaining block time
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// Load Slim + DI container wiring
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// Load dynamic config
$config = $guard->getConfig();
$window = $config->windowSeconds();
$maxFailures = $config->maxFailures();

echo "\n=== Slim Example #2 — AUTO BLOCK ===\n\n";
echo "Config: maxFailures = {$maxFailures}, blockSeconds = {$config->blockSeconds()}\n\n";

$ip = '192.168.100.50';
$subject = 'login_auto_block';

for ($i = 1; $i <= $maxFailures + 3; $i++) {
    echo "Attempt #{$i}...\n";

    $dto = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $window,
        userAgent : 'CLI',
        context   : ['attempt' => $i]
    );

    $result = $guard->handleAttempt($dto, false);

    // If auto-block triggered
    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
        echo "🚫 AUTO BLOCK TRIGGERED at attempt {$i}\n";
        echo "Remaining block seconds: {$remaining}\n\n";
        break;
    }

    echo "Failure count = {$result}\n\n";
}
