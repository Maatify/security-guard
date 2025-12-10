<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:12
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$dispatcher = new SyncDispatcher();

$dispatcher->addClosure(function (SecurityEventDTO $event) {
    echo "[SYNC] {$event->action->value} from {$event->ip}\n";
});

$guard->setEventDispatcher($dispatcher);

// Trigger
$guard->recordFailure(
    LoginAttemptDTO::now('5.5.5.5', 'sync-laravel', 60)
);
