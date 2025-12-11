<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:09
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

// ---------------------------------------------------------------------
// Load Phase 5 Security Guard
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — CUSTOM CONFIG Example (STRICT) ===\n\n";

// ---------------------------------------------------------------------
// ADMIN security policy (stricter)
// ---------------------------------------------------------------------
$adminConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 20,                     // short window
        blockSeconds         : 900,                    // long block (15 min)
        maxFailures          : 3,                      // strict (3 attempts max)
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : "admin:",
        backoffEnabled       : true,
        initialBackoffSeconds: 120,
        backoffMultiplier    : 2.0,
        maxBackoffSeconds    : 3600
    )
);

// ---------------------------------------------------------------------
// CUSTOMER security policy (normal)
// ---------------------------------------------------------------------
$customerConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 60,
        blockSeconds         : 300,
        maxFailures          : 5,
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : "customer:",
        backoffEnabled       : true,
        initialBackoffSeconds: 60,
        backoffMultiplier    : 2.0,
        maxBackoffSeconds    : 3600
    )
);

// ---------------------------------------------------------------------
// 🔐 ADMIN FLOW (strict)
// ---------------------------------------------------------------------
echo "=== ADMIN FLOW ===\n";

$guard->setConfig($adminConfig);

$adminIp = "10.0.0.50";
$adminSubject = "admin_login";

$adminAttempt = LoginAttemptDTO::now(
    ip        : $adminIp,
    subject   : $adminSubject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : "CLI",
    context   : ['flow' => 'admin']
);

$adminResult = $guard->handleAttempt($adminAttempt, false);

echo "Admin failure count = {$adminResult}\n\n";

// ---------------------------------------------------------------------
// 👤 CUSTOMER FLOW (normal)
// ---------------------------------------------------------------------
echo "=== CUSTOMER FLOW ===\n";

$guard->setConfig($customerConfig);

$customerIp = "192.168.1.10";
$customerSubject = "customer_login";

$customerAttempt = LoginAttemptDTO::now(
    ip        : $customerIp,
    subject   : $customerSubject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : "CLI",
    context   : ['flow' => 'customer']
);

$customerResult = $guard->handleAttempt($customerAttempt, false);

echo "Customer failure count = {$customerResult}\n\n";

echo "=== END CUSTOM CONFIG EXAMPLE ===\n\n";
