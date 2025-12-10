<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:14
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

// ---------------------------------------------------------------------
// 📝 Use Monolog as PSR logger
// ---------------------------------------------------------------------

$logger = new Logger('security');
$logger->pushHandler(new StreamHandler(__DIR__ . '/security.log', Logger::INFO));

$dispatcher = new PsrLoggerDispatcher($logger);

$guard->setEventDispatcher($dispatcher);

// Trigger something
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard->recordFailure(
    LoginAttemptDTO::now('9.9.9.9', 'bot-user', 30)
);

echo "Event logged to security.log\n";
