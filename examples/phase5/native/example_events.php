<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:20
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;

$guard = require __DIR__ . '/bootstrap.php';

/**
 * Simple console logger for Security Events
 */
final class ConsoleEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(SecurityEventDTO $event): void
    {
        echo "================ SECURITY EVENT ================\n";
        echo "ID       : {$event->eventId}\n";
        echo "Action   : {$event->action->value}\n";
        echo "Platform : {$event->platform->value}\n";
        echo "IP       : {$event->ip}\n";
        echo "Subject  : {$event->subject}\n";
        echo "Time     : " . date('Y-m-d H:i:s', $event->timestamp) . "\n";

        if ($event->userId !== null) {
            echo "User ID  : {$event->userId}\n";
        }

        if ($event->userType !== null) {
            echo "UserType : {$event->userType}\n";
        }

        if (!empty($event->context)) {
            echo "Context  : " . json_encode($event->context, JSON_PRETTY_PRINT) . "\n";
        }

        echo "================================================\n\n";
    }
}

$guard->setEventDispatcher(new ConsoleEventDispatcher());

$ip = '192.168.5.10';
$subject = 'login';

// Create a login attempt DTO
$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: 60,
    userAgent : 'Mozilla/5.0',
    context   : [
        'route' => '/login',
        'method' => 'POST',
        'meta' => [
            'browser' => 'Firefox',
            'os' => 'macOS'
        ]
    ]
);

// Trigger failure → produces security event
$guard->handleAttempt($attempt, false);


