<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:29
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

require __DIR__ . '/bootstrap_security_guard.php';

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

// ---------------------------------------------------------------------
// POST /login – Simulate failed login attempt
// ---------------------------------------------------------------------
$app->post('/login', function (ServerRequestInterface $request) use ($guard) {
    $body = $request->getParsedBody() ?? [];
    $subject = $body['email'] ?? 'unknown@example.com';
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
    $ua = $request->getHeaderLine('User-Agent') ? : null;

    // Build attempt DTO
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: 900,   // 15 minutes
        userAgent : $ua,
        context   : [
            'route'  => '/login',
            'method' => 'POST',
        ]
    );

    // 1) Check if blocked
    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);

        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'error'     => 'Too many attempts — blocked.',
            'remaining' => $remaining,
        ]));

        return $response->withStatus(429)
            ->withHeader('Content-Type', 'application/json');
    }

    // 2) Always simulate failed login
    $count = $guard->recordFailure($attempt);

    $response = new \Slim\Psr7\Response();

    if ($count >= 5) {
        $response->getBody()->write(json_encode([
            'error' => 'Too many attempts — you are now blocked.',
            'count' => $count,
        ]));

        return $response->withStatus(429)
            ->withHeader('Content-Type', 'application/json');
    }

    // Normal failure
    $response->getBody()->write(json_encode([
        'error' => 'Invalid credentials',
        'count' => $count,
    ]));

    return $response->withStatus(401)
        ->withHeader('Content-Type', 'application/json');
});

// ---------------------------------------------------------------------
$app->run();
