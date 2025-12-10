<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:22
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap.php';

/**
 * Admin login rules (stricter)
 */
$adminConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds         : 20,
        blockSeconds          : 900,
        maxFailures           : 3,
        identifierMode        : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix             : 'admin:',
        backoffEnabled        : true,
        initialBackoffSeconds : 60,
        backoffMultiplier     : 2.0,
        maxBackoffSeconds     : 1800
    )
);

/**
 * Customer login rules (normal)
 */
$customerConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds         : 60,
        blockSeconds          : 300,
        maxFailures           : 5,
        identifierMode        : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix             : 'cust:',
        backoffEnabled        : true,
        initialBackoffSeconds : 60,
        backoffMultiplier     : 2.0,
        maxBackoffSeconds     : 1800
    )
);

// ================================
// 🔐 ADMIN LOGIN FLOW
// ================================
echo "\n===== ADMIN LOGIN FLOW =====\n\n";

$guard->setConfig($adminConfig);

$adminIp = "10.0.0.50";
$adminSubject = "admin_login";

$attempt = LoginAttemptDTO::now(
    ip        : $adminIp,
    subject   : $adminSubject,
    resetAfter: 60,
    userAgent : "CLI",
    context   : ['flow' => 'admin']
);

$result = $guard->handleAttempt($attempt, false);

echo "Admin attempt → failure count: {$result}\n";


// ================================
// 👤 CUSTOMER LOGIN FLOW
// ================================
echo "\n===== CUSTOMER LOGIN FLOW =====\n\n";

$guard->setConfig($customerConfig);

$customerIp = "192.168.1.10";
$customerSubject = "customer_login";

$attempt = LoginAttemptDTO::now(
    ip        : $customerIp,
    subject   : $customerSubject,
    resetAfter: 60,
    userAgent : "CLI",
    context   : ['flow' => 'customer']
);

$result = $guard->handleAttempt($attempt, false);

echo "Customer attempt → failure count: {$result}\n";


