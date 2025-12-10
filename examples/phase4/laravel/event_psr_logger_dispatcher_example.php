<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:10
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Illuminate\Http\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

$logger = new Logger('security-guard');
$logger->pushHandler(new StreamHandler(__DIR__ . '/security.log'));

$dispatcher = new PsrLoggerDispatcher($logger);
$guard->setEventDispatcher($dispatcher);

// Trigger event
$attempt = LoginAttemptDTO::now(
    ip        : '192.0.2.50',
    subject   : 'logger@example.com',
    resetAfter: 60
);

$guard->recordFailure($attempt);

echo "Logged in security.log\n";
