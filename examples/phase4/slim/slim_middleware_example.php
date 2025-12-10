<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:36
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;

require __DIR__ . '/../native/bootstrap_security_guard.php';

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/../native/bootstrap_security_guard.php';

// ----------------------------------------------------
// Attach Dispatcher for events
// ----------------------------------------------------
$dispatcher = new SyncDispatcher();

$dispatcher->addClosure(function (SecurityEventDTO $event) {
    echo "[SLIM EVENT] {$event->action->value} from {$event->ip}\n";
});

$guard->setEventDispatcher($dispatcher);

// ----------------------------------------------------
// Slim Setup
// ----------------------------------------------------
$app = AppFactory::create();

// ----------------------------------------------------
// Security Middleware
// ----------------------------------------------------
$app->add(function (ServerRequestInterface $request, $handler) use ($guard) {
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = $request->getParsedBody()['username'] ?? 'unknown';

    // Check if blocked
    if ($guard->isBlocked($ip, $subject)) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'error' => 'Too many attempts — you are blocked.'
        ]));

        return $response->withStatus(429)
            ->withHeader('Content-Type', 'application/json');
    }

    return $handler->handle($request);
});

// ----------------------------------------------------
// Login Route Example
// ----------------------------------------------------
$app->post('/login', function (ServerRequestInterface $request) use ($guard) {
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = $request->getParsedBody()['username'] ?? 'unknown';

    // This simulates a failed login attempt
    $attempt = LoginAttemptDTO::now($ip, $subject, resetAfter: 60);
    $guard->recordFailure($attempt);

    $response = new \Slim\Psr7\Response();
    $response->getBody()->write(json_encode([
        'message' => 'Login attempt recorded'
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

// ----------------------------------------------------
$app->run();
