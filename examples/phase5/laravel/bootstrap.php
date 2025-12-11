<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:02
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Example: Bootstrap SecurityGuardService for Laravel-style apps
 *
 * This file mirrors the Phase 4 pattern:
 * - Load adapter
 * - Build security config
 * - Build identifier strategy
 * - Build SecurityGuardService instance
 * - Return ready-to-use $guard for all Laravel examples
 */

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;

use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Service\SecurityGuardService;

require __DIR__ . '/../../../vendor/autoload.php';

// ---------------------------------------------------------------------
// 🔧 Load DataAdapters configuration (mysql / redis / mongo)
// ---------------------------------------------------------------------
$config = new EnvironmentConfig(__DIR__ . '/../../../');
$resolver = new DatabaseResolver($config);

// 🟣 Use: redis.security profile (from data-adapters config)
$redisAdapter = $resolver->resolve('redis.security', autoConnect: true);

// ---------------------------------------------------------------------
// 🛡 Security Guard thresholds (Phase 5 config)
// ---------------------------------------------------------------------
$securityConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 60,                     // Count login attempts inside 60 seconds
        blockSeconds         : 300,                    // Auto-block for 5 minutes
        maxFailures          : 5,                      // Block after 5 failures
        identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix            : 'sg:',                  // Namespace prefix

        // Adaptive backoff system (enabled in Phase 5)
        backoffEnabled       : true,
        initialBackoffSeconds: 60,                     // Start at 60s
        backoffMultiplier    : 2.0,                    // Exponential growth
        maxBackoffSeconds    : 3600                    // Hard cap = 1 hour
    )
);

$strategy = new DefaultIdentifierStrategy($securityConfig);

// ---------------------------------------------------------------------
// 🎯 Build the SecurityGuardService instance
// ---------------------------------------------------------------------
$guard = new SecurityGuardService(
    adapter : $redisAdapter,
    strategy: $strategy
);

// Assign default config (allows overriding in examples)
$guard->setConfig($securityConfig);

// ---------------------------------------------------------------------
// Return ready-to-use instance for all Laravel examples
// ---------------------------------------------------------------------
return $guard;
