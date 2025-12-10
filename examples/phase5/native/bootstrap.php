<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-12-11 01:14
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/security-guard
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;

use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Service\SecurityGuardService;

// ---------------------------------------------------------------------
// 🔧 Load adapter configuration from .env
// ---------------------------------------------------------------------
$config = new EnvironmentConfig(__DIR__ . '/../../../');
$resolver = new DatabaseResolver($config);

// ---------------------------------------------------------------------
// 🟢 Resolve Redis Security Adapter (Phase 5)
// ---------------------------------------------------------------------
$redisAdapter = $resolver->resolve('redis.security', autoConnect: true);

// ---------------------------------------------------------------------
// ⚙️ Build the Security Config (Phase 5)
// ---------------------------------------------------------------------
$dto = new SecurityConfigDTO(
    windowSeconds:         60,           // Count failures inside 60 seconds
    blockSeconds:          300,          // Auto-block for 5 minutes
    maxFailures:           5,            // Block after 5 failed attempts
    identifierMode:        IdentifierModeEnum::IDENTIFIER_AND_IP,
    keyPrefix:             'sg:',        // Namespace prefix for redis keys

    // Backoff configuration
    backoffEnabled:        true,
    initialBackoffSeconds: 300,
    backoffMultiplier:     2.0,
    maxBackoffSeconds:     3600
);

$defaultConfig = new SecurityConfig($dto);

// ---------------------------------------------------------------------
// 🔐 Build Identifier Strategy
// ---------------------------------------------------------------------
$strategy = new DefaultIdentifierStrategy($defaultConfig);

// ---------------------------------------------------------------------
// 🚀 Build SecurityGuardService (Phase 5 Orchestration)
// ---------------------------------------------------------------------
$guard = new SecurityGuardService(
    adapter: $redisAdapter,
    strategy: $strategy
);

// Override default config inside the service
$guard->setConfig($defaultConfig);

// Return service instance for other examples
return $guard;
