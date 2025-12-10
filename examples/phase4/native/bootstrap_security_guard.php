<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:12
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;

use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Service\SecurityGuardService;

require __DIR__ . '/../../../vendor/autoload.php';

// ---------------------------------------------------------------------
// 🔧 Load adapter configuration
// ---------------------------------------------------------------------
$config = new EnvironmentConfig(__DIR__ . '/../../../');
$resolver = new DatabaseResolver($config);

// ---------------------------------------------------------------------
// 🟢 Use redis.security profile for Phase 4 examples
// ---------------------------------------------------------------------
$redisAdapter = $resolver->resolve('redis.security', autoConnect: true);

// ---------------------------------------------------------------------
// ⚙️ Build SecurityConfigDTO (REQUIRED)
// ---------------------------------------------------------------------
$dto = new SecurityConfigDTO(
    windowSeconds:        60,                     // Count failures inside a 60s window
    blockSeconds:         300,                    // Default block = 5 minutes
    maxFailures:          5,                      // Block after 5 failures
    identifierMode:       IdentifierModeEnum::IDENTIFIER_AND_IP, // Typical mode
    keyPrefix:            'sg:',                  // Namespace prefix for Redis keys

    // Backoff configuration
    backoffEnabled:       true,
    initialBackoffSeconds: 300,                   // Start at 5 minutes
    backoffMultiplier:    2.0,                    // Exponential growth
    maxBackoffSeconds:    3600                    // Cap at 1 hour
);

// ---------------------------------------------------------------------
// 🔐 Build SecurityConfig from DTO
// ---------------------------------------------------------------------
$securityConfig = new SecurityConfig($dto);

// ---------------------------------------------------------------------
// 🔐 Build Identifier Strategy (REQUIRES SecurityConfig)
// ---------------------------------------------------------------------
$strategy = new DefaultIdentifierStrategy($securityConfig);

// ---------------------------------------------------------------------
// 🚀 Build the Security Guard Service
// ---------------------------------------------------------------------
$guard = new SecurityGuardService(
    adapter: $redisAdapter,
    strategy: $strategy
);

// Make the guard available to calling scripts
return $guard;

