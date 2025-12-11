<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:28
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim PRO Example #3
 * BRUTE FORCE ATTACK SIMULATION (STRICT)
 *
 * Demonstrates:
 *  - How Phase 5 handles rapid brute force attempts
 *  - Automatic blocking when thresholds are exceeded
 *  - No custom logic — engine decides everything
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

// -------------------------------------------------------------
// ATTACK-FOCUSED CONFIG (STRICT)
// -------------------------------------------------------------
$dto = new SecurityConfigDTO(
    windowSeconds        : 30,           // small window → quick accumulation
    blockSeconds         : 300,          // 5 minutes
    maxFailures          : 5,            // block after 5 attempts
    identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
    keyPrefix            : "brute:",
    backoffEnabled       : true,
    initialBackoffSeconds: 10,
    backoffMultiplier    : 2.0,
    maxBackoffSeconds    : 120
);

$guard->setConfig(new SecurityConfig($dto));

echo "\n=== PRO Example #3 — BRUTE FORCE SIMULATION (STRICT) ===\n\n";

$window = $dto->windowSeconds;

// =============================================================
// SIMULATE ATTACKER TRAFFIC
// =============================================================
$ip = "185.199.110.55";
$subject = "victim@example.com";

echo "Attacker IP:     {$ip}\n";
echo "Victim Subject:  {$subject}\n";
echo "Threshold:       {$dto->maxFailures} failures\n\n";

for ($i = 1; $i <= 20; $i++) {
    echo "→ Attack Attempt #{$i}\n";

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $window,
        userAgent : "CLI",
        context   : [
            'scenario' => 'brute_force',
            'attempt'  => $i
        ]
    );

    $result = $guard->handleAttempt($attempt, false);

    // If the engine blocks the attacker
    if ($guard->isBlocked($ip, $subject)) {
        $rem = $guard->getRemainingBlockSeconds($ip, $subject);

        echo "🚫 BLOCKED after attempt {$i}\n";
        echo "Remaining block time: {$rem} seconds\n\n";

        echo "Engine stopped the brute force attack automatically.\n";
        echo "=== END OF BRUTE FORCE SIMULATION ===\n\n";
        break;
    }

    echo "Failure count = {$result}\n\n";
}

