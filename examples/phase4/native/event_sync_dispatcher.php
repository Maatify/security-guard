<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:13
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

// ---------------------------------------------------------------------
// 🟣 Register a closure listener
// ---------------------------------------------------------------------

$dispatcher = new SyncDispatcher();

$dispatcher->addClosure(function (SecurityEventDTO $event) {
    echo "[EVENT] {$event->action->value} from {$event->ip}\n";
});

$guard->setEventDispatcher($dispatcher);

// ---------------------------------------------------------------------
// 🔥 Trigger an event by running a failure
// ---------------------------------------------------------------------

$attempt = LoginAttemptDTO::now(
    ip        : '1.2.3.4',
    subject   : 'attacker',
    resetAfter: 30
);

$guard->recordFailure($attempt);

