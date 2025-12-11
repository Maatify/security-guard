<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:15
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim PRO Example #1
 * ADAPTIVE BACKOFF SIMULATION (STRICT)
 *
 * Demonstrates:
 *  - How SecurityGuardService escalates punishment after repeated failures
 *  - Each failure increases the effective cooldown implicitly
 *  - No manual backoff logic — strictly using Phase 5 API
 */

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim App + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/../bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// ======================================================================
// APPLY A SPECIAL BACKOFF-FOCUSED CONFIG
// ======================================================================
$backoffConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 60,
        blockSeconds         : 120,            // after a threshold is reached
        maxFailures          : 5,
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : "bf:",
        backoffEnabled       : true,
        initialBackoffSeconds: 10,             // start small
        backoffMultiplier    : 2.0,            // exponential growth
        maxBackoffSeconds    : 120             // capped at 120 seconds
    )
);

$guard->setConfig($backoffConfig);

echo "\n=== PRO Example #1 — ADAPTIVE BACKOFF SIMULATION (STRICT) ===\n\n";

// Load effective config values
$window = $backoffConfig->windowSeconds();

$ip = "198.51.100.55";
$subject = "adaptive_backoff_test";

// ======================================================================
// SIMULATE REPEATED FAILURES
// ======================================================================

$attemptNumber = 0;

while (++$attemptNumber <= 7) {
    echo "→ Attempt #{$attemptNumber} (failure)\n";

    $dto = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $window,
        userAgent : "CLI",
        context   : ['pro' => 'adaptive_backoff', 'attempt' => $attemptNumber]
    );

    $result = $guard->handleAttempt($dto, false);

    // If the system has decided to block a user:
    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);

        echo "🚫 BLOCK ACTIVATED — remaining: {$remaining} sec\n";
        echo "Simulation ended.\n\n";
        break;
    }

    echo "Failure count returned: {$result}\n";
    echo "Backoff escalation handled internally by Phase 5 engine.\n\n";

}

echo "=== END OF ADAPTIVE BACKOFF SIMULATION ===\n\n";
