<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:30
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim PRO Example #4
 * DISTRIBUTED ATTACK SIMULATION (STRICT)
 *
 * Demonstrates:
 *  - Multiple attackers from different IPs
 *  - All targeting the same account (subject)
 *  - How Phase 5 detects cross-IP attack patterns automatically
 *  - How block() applies regardless of attacker IP
 */

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim app + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/../bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// -------------------------------------------------------------
// HIGH-SENSITIVITY CONFIG (STRICT)
// -------------------------------------------------------------
$dto = new SecurityConfigDTO(
    windowSeconds        : 60,
    blockSeconds         : 600, // 10 minutes
    maxFailures          : 5,
    identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
    keyPrefix            : "dist:",
    backoffEnabled       : true,
    initialBackoffSeconds: 20,
    backoffMultiplier    : 2.0,
    maxBackoffSeconds    : 300
);

$guard->setConfig(new SecurityConfig($dto));

echo "\n=== PRO Example #4 — DISTRIBUTED ATTACK SIMULATION (STRICT) ===\n\n";

// Victim account
$subject = "victim@example.com";

// Botnet list
$attackIps = [
    "102.55.22.10",
    "185.77.90.44",
    "91.201.30.88",
    "203.0.113.77",
    "198.51.100.66",
    "45.85.190.23",
];

echo "Target subject: {$subject}\n";
echo "Botnet attackers: " . count($attackIps) . " IPs\n\n";

$window = $dto->windowSeconds;

// -------------------------------------------------------------
// SIMULATE THE ATTACK
// -------------------------------------------------------------
$attemptNo = 0;

foreach ($attackIps as $attackerIp) {
    $attemptNo++;
    echo "→ Attack Attempt #{$attemptNo} from IP {$attackerIp}\n";

    $dtoAttempt = LoginAttemptDTO::now(
        ip        : $attackerIp,
        subject   : $subject,
        resetAfter: $window,
        userAgent : "BOT/1.0",
        context   : [
            'pro'        => 'distributed_attack',
            'bot_source' => $attackerIp,
            'seq'        => $attemptNo,
        ]
    );

    $result = $guard->handleAttempt($dtoAttempt, false);

    // Has the system detected distributed behaviour?
    if ($guard->isBlocked($attackerIp, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($attackerIp, $subject);

        echo "🚫 BLOCKED — Target account is locked\n";
        echo "Remaining block time: {$remaining} sec\n\n";

        echo "Distributed attack successfully mitigated.\n";
        echo "=== END OF DISTRIBUTED ATTACK SIMULATION ===\n\n";
        break;
    }

    echo "Failure count returned: {$result}\n\n";
}
