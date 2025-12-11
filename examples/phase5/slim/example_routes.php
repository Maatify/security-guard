<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:33
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example
 * ROUTES INTEGRATION (STRICT)
 *
 * Demonstrates:
 *  - How real Slim routes integrate with SecurityGuardService
 *  - login, admin login, and API token endpoints
 *  - Block detection per-route
 *  - handleAttempt() + proper DTO construction
 */

use Slim\Factory\AppFactory;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$app = require __DIR__ . '/bootstrap.php';

/** @var SecurityGuardService $guard */
$guard = $app->getContainer()->get(SecurityGuardService::class);

echo "\n=== Slim Phase 5 — Route Integration Example (STRICT) ===\n\n";

// -------------------------------------------------------------
// Reusable function for request blocking
// -------------------------------------------------------------
$checkBlock = function ($ip, $subject, SecurityGuardService $guard) {
    if ($guard->isBlocked($ip, $subject)) {
        $rem = $guard->getRemainingBlockSeconds($ip, $subject);

        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'blocked'   => true,
            'remaining' => $rem,
            'ip'        => $ip,
            'subject'   => $subject
        ]));

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(429);
    }

    return null;
};

// -------------------------------------------------------------
// LOGIN ROUTE (standard user login)
// -------------------------------------------------------------
$app->post('/login', function ($request, $response) use ($app, $guard, $checkBlock) {
    $body = $request->getParsedBody();
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = $body['username'] ?? 'login';

    // 1) Check blocked?
    if ($blockedResp = $checkBlock($ip, $subject, $guard)) {
        return $blockedResp;
    }

    // 2) build attempt DTO
    $dto = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : $request->getHeaderLine('User-Agent'),
        context   : ['route' => '/login']
    );

    // simulate BAD password
    $result = $guard->handleAttempt($dto, false);

    $response->getBody()->write(json_encode([
        'status'       => 'failed',
        'failureCount' => $result
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

// -------------------------------------------------------------
// ADMIN LOGIN ROUTE (restricted + extra logging)
// -------------------------------------------------------------
$app->post('/admin/login', function ($request, $response) use ($guard, $checkBlock) {
    $body = $request->getParsedBody();
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = 'admin_' . ($body['username'] ?? 'admin');

    if ($blockedResp = $checkBlock($ip, $subject, $guard)) {
        return $blockedResp;
    }

    // build attempt DTO
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : $request->getHeaderLine('User-Agent'),
        context   : ['route' => '/admin/login', 'admin' => true]
    );

    // simulate ADMIN login failure
    $result = $guard->handleAttempt($attempt, false);

    $response->getBody()->write(json_encode([
        'admin'        => true,
        'status'       => 'failed',
        'failureCount' => $result
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

// -------------------------------------------------------------
// API TOKEN ROUTE (STRICT)
// -------------------------------------------------------------
$app->post('/api/token', function ($request, $response) use ($guard, $checkBlock) {
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = "api_token_request";

    if ($blockedResp = $checkBlock($ip, $subject, $guard)) {
        return $blockedResp;
    }

    $dto = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : $request->getHeaderLine('User-Agent'),
        context   : ['route' => '/api/token']
    );

    $result = $guard->handleAttempt($dto, false); // simulate wrong API key

    $response->getBody()->write(json_encode([
        'status'   => 'token_denied',
        'failures' => $result
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

// -------------------------------------------------------------
// READY
// -------------------------------------------------------------
echo "Slim example routes registered.\n";

return $app;
