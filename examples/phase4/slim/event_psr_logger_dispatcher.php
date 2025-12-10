<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:02
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

$logger = new Logger('security-guard');
$logger->pushHandler(new StreamHandler(__DIR__ . '/security.log'));

$dispatcher = new PsrLoggerDispatcher($logger);
$guard->setEventDispatcher($dispatcher);

$app->get('/log-event', function () use ($guard) {
    $attempt = LoginAttemptDTO::now('9.9.9.9', 'bot@example.com', 60);

    $guard->recordFailure($attempt);

    echo "Logged to security.log";
});

$app->run();
