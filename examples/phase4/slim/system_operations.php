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

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

$app->get('/system-ops', function () use ($guard) {
    // simulate activity
    $guard->recordFailure(
        \Maatify\SecurityGuard\DTO\LoginAttemptDTO::now('10.0.0.20', 'sys@example.com', 60)
    );

    $stats = $guard->getStats();

    $guard->resetAttempts('10.0.0.20', 'sys@example.com');
    $guard->cleanup();

    return json_encode([
        'stats'   => $stats,
        'message' => 'Ops completed'
    ]);
});

$app->run();
