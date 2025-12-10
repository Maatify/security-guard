<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

require __DIR__ . '/bootstrap_security_guard.php';
$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

// ------------------------------------------------------------
// POST /login-failed
// ------------------------------------------------------------
$app->post('/login-failed', function (ServerRequestInterface $req, ResponseInterface $res) use ($guard) {
    $ip = $req->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = $req->getParsedBody()['email'] ?? 'unknown@example.com';

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: 60,
        userAgent : $req->getHeaderLine('User-Agent'),
        context   : ['route' => '/login-failed']
    );

    $count = $guard->recordFailure($attempt);

    $res->getBody()->write(json_encode([
        'failures' => $count,
    ]));

    return $res->withHeader('Content-Type', 'application/json');
});

$app->run();
