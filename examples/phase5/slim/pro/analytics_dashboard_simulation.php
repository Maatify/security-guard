<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:18
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim PRO Example #2
 * ANALYTICS DASHBOARD SIMULATION (STRICT)
 *
 * Demonstrates:
 *  - How Phase 5 can feed real-time analytics dashboards
 *  - Using getStats() to obtain aggregated driver metrics
 *  - Simulating realistic traffic patterns (failures and successes)
 *  - Zero custom logic — STRICT Phase 5 API only
 */

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/../bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

// -------------------------------------------------------------
// APPLY analytics-focused config
// -------------------------------------------------------------
$dto = new SecurityConfigDTO(
    windowSeconds        : 60,
    blockSeconds         : 300,
    maxFailures          : 5,
    identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
    keyPrefix            : "analytics:",
    backoffEnabled       : true,
    initialBackoffSeconds: 30,
    backoffMultiplier    : 2.0,
    maxBackoffSeconds    : 300
);

$guard->setConfig(new SecurityConfig($dto));

echo "\n=== PRO Example #2 — ANALYTICS DASHBOARD SIMULATION (STRICT) ===\n\n";

// resetAfter window
$window = $dto->windowSeconds;

// -------------------------------------------------------------
// SIMULATE TRAFFIC
// -------------------------------------------------------------
$traffic = [
    ['ip' => '203.0.113.100', 'subject' => 'user_A', 'success' => false],
    ['ip' => '203.0.113.100', 'subject' => 'user_A', 'success' => false],
    ['ip' => '203.0.113.100', 'subject' => 'user_A', 'success' => true],
    ['ip' => '198.51.100.77', 'subject' => 'user_B', 'success' => false],
    ['ip' => '198.51.100.77', 'subject' => 'user_B', 'success' => false],
    ['ip' => '198.51.100.77', 'subject' => 'user_B', 'success' => false],
    ['ip' => '198.51.100.77', 'subject' => 'user_B', 'success' => false],
    ['ip' => '198.51.100.77', 'subject' => 'user_B', 'success' => false],
];

echo "Simulating traffic...\n\n";

foreach ($traffic as $entry) {
    $dtoAttempt = LoginAttemptDTO::now(
        ip        : $entry['ip'],
        subject   : $entry['subject'],
        resetAfter: $window,
        userAgent : "CLI",
        context   : ['analytics' => true]
    );

    $result = $guard->handleAttempt($dtoAttempt, $entry['success']);

    echo ($entry['success'] ? "✔ SUCCESS" : "✖ FAILURE") .
         " — {$entry['ip']} / {$entry['subject']} | ";

    echo "Returned: " . var_export($result, true) . "\n";
}

echo "\n";

// -------------------------------------------------------------
// RETRIEVE & DISPLAY AGGREGATED METRICS
// -------------------------------------------------------------
echo "Retrieving aggregated stats...\n\n";

$stats = $guard->getStats();

/**
 * We don't assume a specific structure — STRICT MODE:
 * just dump what the driver exposes.
 */
echo json_encode($stats, JSON_PRETTY_PRINT) . "\n\n";

echo "=== END OF ANALYTICS SIMULATION ===\n\n";
