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
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

$app->post('/check-block', function (\Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res) use ($guard) {
    $ip = '203.0.113.77';
    $subject = 'slim-block@example.com';

    $attempt = LoginAttemptDTO::now($ip, $subject, 60);

    for ($i = 1; $i <= 5; $i++) {
        $guard->recordFailure($attempt);
    }

    if ($guard->isBlocked($ip, $subject)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
        $msg = "Blocked — remaining: {$remaining}s";
    } else {
        $msg = "Not blocked";
    }

    $res->getBody()->write($msg);

    return $res;
});

$app->run();
