<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:03
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example #4
 * CUSTOM CONFIG LOGIC (STRICT)
 *
 * This example demonstrates:
 *  - Switching between multiple configurations (admin → customer)
 *  - Using the full 9-parameter SecurityConfigDTO
 *  - Applying handleAttempt() for each flow
 *  - Maintaining strict parity with the Native Phase 5 examples
 */

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// -------------------------------------------------------------
// Load Slim + DI container
// -------------------------------------------------------------
$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

echo "\n=== Slim Example #4 — CUSTOM CONFIG LOGIC (STRICT) ===\n\n";

// =====================================================================
// 1) ADMIN CONFIG — stricter security rules
// =====================================================================

$adminConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 20,
        blockSeconds         : 900,
        maxFailures          : 3,
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : 'admin:',
        backoffEnabled       : true,
        initialBackoffSeconds: 300,
        backoffMultiplier    : 2.0,
        maxBackoffSeconds    : 3600
    )
);

echo "→ Applying ADMIN security policy...\n";
$guard->setConfig($adminConfig);

$adminIp = '10.10.10.22';
$adminSubject = 'admin_login';

// First admin failure
$adminAttempt = LoginAttemptDTO::now(
    ip        : $adminIp,
    subject   : $adminSubject,
    resetAfter: $adminConfig->windowSeconds(),
    userAgent : 'CLI',
    context   : ['flow' => 'admin']
);

$adminResult = $guard->handleAttempt($adminAttempt, false);

echo "ADMIN failure #1 → count = {$adminResult}\n\n";


// =====================================================================
// 2) CUSTOMER CONFIG — more lenient rules
// =====================================================================

$customerConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 60,
        blockSeconds         : 300,
        maxFailures          : 5,
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : 'cust:',
        backoffEnabled       : true,
        initialBackoffSeconds: 300,
        backoffMultiplier    : 2.0,
        maxBackoffSeconds    : 3600
    )
);

echo "→ Applying CUSTOMER security policy...\n";
$guard->setConfig($customerConfig);

$customerIp = '192.168.1.33';
$customerSubject = 'customer_login';

$customerAttempt = LoginAttemptDTO::now(
    ip        : $customerIp,
    subject   : $customerSubject,
    resetAfter: $customerConfig->windowSeconds(),
    userAgent : 'CLI',
    context   : ['flow' => 'customer']
);

$customerResult = $guard->handleAttempt($customerAttempt, false);

echo "CUSTOMER failure #1 → count = {$customerResult}\n\n";

echo "=== END OF CUSTOM CONFIG EXAMPLE ===\n\n";
