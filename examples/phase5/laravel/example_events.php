<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:14
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;

// ---------------------------------------------------------------------
// Load security guard
// ---------------------------------------------------------------------
$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — EVENTS Example (STRICT) ===\n\n";

// ---------------------------------------------------------------------
// ATTACH A CUSTOM DISPATCHER
// This listener simply prints the event
// ---------------------------------------------------------------------
$guard->setEventDispatcher(new class {
    public function dispatch(\Maatify\SecurityGuard\DTO\SecurityEventDTO $event): void
    {
        echo "📡 EVENT RECEIVED:\n";
        echo "  Action:    " . $event->action->value . "\n";
        echo "  Platform:  " . $event->platform->value . "\n";
        echo "  IP:        " . $event->ip . "\n";
        echo "  Subject:   " . $event->subject . "\n";
        echo "  Timestamp: " . $event->timestamp . "\n";

        if (! empty($event->context)) {
            echo "  Context:\n";
            foreach ($event->context as $k => $v) {
                echo "    - {$k}: " . json_encode($v) . "\n";
            }
        }

        echo "\n";
    }
});

// ---------------------------------------------------------------------
// STEP 1 — Generate a login failure event (automatically via handleAttempt)
// ---------------------------------------------------------------------
$ip = '172.30.33.50';
$subject = 'events_demo_user';

$attempt1 = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: $guard->getConfig()->windowSeconds(),
    userAgent : 'CLI',
    context   : ['step' => 1]
);

$guard->handleAttempt($attempt1, false);

// ---------------------------------------------------------------------
// STEP 2 — Trigger a custom event manually
// ---------------------------------------------------------------------
$customEvent = SecurityEventFactory::custom(
    action  : SecurityAction::custom('api_misuse'),
    platform: SecurityPlatform::custom('laravel-artisan'),
    ip      : '10.10.10.10',
    subject : 'artisan-user',
    context : ['info' => 'manual event fired']
);

echo "➡ Triggering custom event...\n\n";
$guard->handleEvent($customEvent);

// ---------------------------------------------------------------------
// STEP 3 — Trigger a cleanup event
// ---------------------------------------------------------------------
echo "➡ Triggering cleanup event...\n\n";
$guard->cleanup();

echo "\n=== END EVENTS EXAMPLE ===\n\n";
