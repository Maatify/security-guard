<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:03
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

$app->get('/custom-event', function () use ($guard) {
    $event = SecurityEventFactory::custom(
        action: SecurityAction::custom('api_misuse'),
        platform: SecurityPlatform::custom('slim-api'),
        ip: '10.0.0.5',
        subject: 'slim-user',
        context: ['endpoint' => '/x/test']
    );

    $guard->setEventDispatcher(new class {
        public function dispatch($event)
        {
            print_r($event);
        }
    });

    $guard->cleanup(); // triggers event

    echo "Custom event fired.";
});

$app->run();
