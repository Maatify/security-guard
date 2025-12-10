<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:01
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

// ---------------------------------------------------------
// Correct usage: addClosure() not addListener()
// ---------------------------------------------------------
$dispatcher = new SyncDispatcher();

$dispatcher->addClosure(function (SecurityEventDTO $event) {
    echo "[SLIM EVENT] {$event->action->value} from {$event->ip}\n";
});

$guard->setEventDispatcher($dispatcher);

// ---------------------------------------------------------
// Route triggers a login failure → event dispatched
// ---------------------------------------------------------
$app->get('/trigger-event', function () use ($guard) {
    $attempt = LoginAttemptDTO::now(
        ip        : '1.2.3.4',
        subject   : 'slim-user',
        resetAfter: 30
    );

    $guard->recordFailure($attempt);

    echo "Event Fired!";
});

$app->run();
