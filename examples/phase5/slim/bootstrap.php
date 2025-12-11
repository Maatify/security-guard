<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:39
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Bootstrap (STRICT)
 *
 * Loads:
 *  - Slim App + DI Container
 *  - Redis Adapter via maatify/data-adapters
 *  - SecurityConfig + SecurityConfigDTO (Phase 5)
 *  - DefaultIdentifierStrategy
 *  - SecurityGuardService (Phase 5, with handleAttempt)
 *
 * NOTE:
 *  This file is included by every Slim Example inside this folder.
 */

use DI\Container;
use Slim\Factory\AppFactory;

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;

use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Service\SecurityGuardService;

require __DIR__ . '/../../../vendor/autoload.php';

// ======================================================
// 1) Initialize Slim + Container
// ======================================================
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register into local variable for convenience
$container = $app->getContainer();

/**
 * ======================================================
 * 2) Register Redis adapter
 * ======================================================
 * Using maatify/data-adapters → DatabaseResolver
 */
$container->set('security.redis', function () {
    $env = new EnvironmentConfig(__DIR__ . '/../../../../');
    $resolver = new DatabaseResolver($env);

    // MUST match DSN key: "redis.security" in your .env config
    return $resolver->resolve('redis.security', autoConnect: true);
});

/**
 * ======================================================
 * 3) Register Default Security Config (Phase 5)
 * ======================================================
 */
$container->set('security.config.default', function () {

    return new SecurityConfig(
        new SecurityConfigDTO(
            windowSeconds:         60,       // failure window (seconds)
            blockSeconds:          300,      // block for 5 minutes
            maxFailures:           5,        // block after 5 failures
            identifierMode:        IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix:             'sg:',

            // Backoff settings (enabled by default)
            backoffEnabled:        true,
            initialBackoffSeconds: 300,
            backoffMultiplier:     2.0,
            maxBackoffSeconds:     3600
        )
    );
});

/**
 * ======================================================
 * 4) Identifier Strategy (requires security.config.default)
 * ======================================================
 */
$container->set('security.strategy', function ($c) {
    return new DefaultIdentifierStrategy(
        $c->get('security.config.default')
    );
});

/**
 * ======================================================
 * 5) SecurityGuardService (Phase 5) FINAL
 * ======================================================
 */
$container->set(SecurityGuardService::class, function ($c) {

    $guard = new SecurityGuardService(
        adapter:  $c->get('security.redis'),
        strategy: $c->get('security.strategy')
    );

    // apply default Phase 5 config
    $guard->setConfig($c->get('security.config.default'));

    return $guard;
});

/**
 * ======================================================
 * EXPORT Slim App
 * ======================================================
 */
return $app;

