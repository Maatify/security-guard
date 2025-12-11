<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:40
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

// Slim container
$container = $app->getContainer();

/**
 * ------------------------------------------------------
 * 🔧 Load Redis Adapter
 * ------------------------------------------------------
 */
$container->set('security.redis', function () {
    $env = new EnvironmentConfig(__DIR__ . '/../../../../');
    $resolver = new DatabaseResolver($env);

    return $resolver->resolve('redis.security', autoConnect: true);
});

/**
 * ------------------------------------------------------
 * 🔧 Default Security Config (Phase 5)
 * ------------------------------------------------------
 */
$container->set('security.config.default', function () {

    return new SecurityConfig(
        new SecurityConfigDTO(
            windowSeconds:         60,
            blockSeconds:          300,
            maxFailures:           5,
            identifierMode:        IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix:             'sg:',
            backoffEnabled:        true,
            initialBackoffSeconds: 300,
            backoffMultiplier:     2.0,
            maxBackoffSeconds:     3600,
        )
    );
});

/**
 * ------------------------------------------------------
 * 🔐 Identifier Strategy (uses the default config)
 * ------------------------------------------------------
 */
$container->set('security.strategy', function ($c) {
    return new DefaultIdentifierStrategy(
        $c->get('security.config.default')
    );
});

/**
 * ------------------------------------------------------
 * 🚀 Build SecurityGuardService (final object)
 * ------------------------------------------------------
 */
$container->set(SecurityGuardService::class, function ($c) {

    $guard = new SecurityGuardService(
        adapter:  $c->get('security.redis'),
        strategy: $c->get('security.strategy')
    );

    // Apply default config at boot
    $guard->setConfig($c->get('security.config.default'));

    return $guard;
});
