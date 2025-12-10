<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:51
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);


/**
 * Example: Bootstrap SecurityGuardService for Laravel-style apps
 *
 * Path: examples/laravel/phase4/bootstrap_security_guard.php
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
// 🔧 Load adapter configuration (mysql / redis / mongo) via data-adapters
// ---------------------------------------------------------------------
$config = new EnvironmentConfig(__DIR__ . '/../../../');
$resolver = new DatabaseResolver($config);

// 🟣 Redis profile used for Security Guard (you configure it in data-adapters)
$redisAdapter = $resolver->resolve('redis.security', autoConnect: true);

// ---------------------------------------------------------------------
// 🛡 Security Guard thresholds (Phase 4 example config)
// ---------------------------------------------------------------------
$securityConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds        : 60,              // 60s sliding window
        blockSeconds         : 300,             // 5m block duration
        maxFailures          : 5,               // max attempts per window
        identifierMode       : IdentifierModeEnum::IP_ONLY,
        keyPrefix            : 'sg:',
        backoffEnabled       : false,
        initialBackoffSeconds: 10,
        backoffMultiplier    : 2.0,
        maxBackoffSeconds    : 300,
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

// This file returns a ready-to-use instance for other examples.
return $guard;
