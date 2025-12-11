<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:17
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — ROUTES Simulation (STRICT) ===\n\n";

/**
 * Very small routing table — simulation only.
 */
$routes = [];

/**
 * Helper: register a route
 */
$routes['GET']['/login'] = function (string $ip, string $username) use ($guard) {
    echo "➡ [ROUTE] /login  user={$username}\n";

    // If blocked → deny access
    if ($guard->isBlocked($ip, $username)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $username);

        return [
            'status'  => 429,
            'message' => "🚫 Too many attempts – retry after {$remaining}s"
        ];
    }

    // Simulate login failure always for demo
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $username,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : 'CLI',
        context   : ['route' => '/login']
    );

    $count = $guard->handleAttempt($attempt, false);

    return [
        'status'          => 401,
        'failed_attempts' => $count,
        'blocked'         => $guard->isBlocked($ip, $username)
    ];
};

$routes['POST']['/login'] = function (string $ip, string $username, bool $success) use ($guard) {
    echo "➡ [ROUTE] POST /login user={$username}\n";

    if ($guard->isBlocked($ip, $username)) {
        $remaining = $guard->getRemainingBlockSeconds($ip, $username);

        return ['status' => 429, 'message' => "Blocked — {$remaining}s left"];
    }

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $username,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : 'CLI',
        context   : ['route' => '/login']
    );

    $result = $guard->handleAttempt($attempt, $success);

    return [
        'status'  => $success ? 200 : 401,
        'result'  => $result,
        'blocked' => $guard->isBlocked($ip, $username)
    ];
};

$routes['POST']['/admin/login'] = function (string $ip, string $username) use ($guard) {
    echo "➡ [ROUTE] POST /admin/login user={$username}\n";

    // Admin attempts hit much stricter config
    $adminConfig = new \Maatify\SecurityGuard\Config\SecurityConfig(
        new \Maatify\SecurityGuard\Config\SecurityConfigDTO(
            windowSeconds        : 20,
            blockSeconds         : 900,
            maxFailures          : 3,
            identifierMode       : \Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix            : 'admin:',
            backoffEnabled       : true,
            initialBackoffSeconds: 60,
            backoffMultiplier    : 2.0,
            maxBackoffSeconds    : 3600
        )
    );

    $guard->setConfig($adminConfig);

    if ($guard->isBlocked($ip, $username)) {
        $remain = $guard->getRemainingBlockSeconds($ip, $username);

        return ['status' => 429, 'message' => "Admin account locked – {$remain}s left"];
    }

    // Simulate admin login failure
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $username,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : 'CLI',
        context   : ['route' => '/admin/login']
    );

    $count = $guard->handleAttempt($attempt, false);

    return [
        'status'          => 401,
        'failed_attempts' => $count,
        'blocked'         => $guard->isBlocked($ip, $username)
    ];
};

$routes['POST']['/api/token'] = function (string $ip, string $clientId, bool $success) use ($guard) {
    echo "➡ [ROUTE] POST /api/token client={$clientId}\n";

    if ($guard->isBlocked($ip, $clientId)) {
        return [
            'status'  => 429,
            'message' => "API client blocked ({$clientId})"
        ];
    }

    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $clientId,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : 'API',
        context   : ['route' => '/api/token']
    );

    $result = $guard->handleAttempt($attempt, $success);

    return [
        'status' => $success ? 200 : 403,
        'result' => $result
    ];
};

// ===================================================================
// TEST ROUTES
// ===================================================================
echo "\n--- Running ROUTE Tests ---\n\n";

// 1) GET /login (fails)
print_r($routes['GET']['/login']('192.168.0.10', 'alice'));

// 2) POST /login success
print_r($routes['POST']['/login']('192.168.0.10', 'alice', true));

// 3) POST /admin/login (fail until block)
for ($i = 1; $i <= 4; $i++) {
    print_r($routes['POST']['/admin/login']('10.0.10.5', 'root'));
}

// 4) POST /api/token failure
print_r($routes['POST']['/api/token']('8.8.8.8', 'client-432', false));

echo "\n=== END ROUTES EXAMPLE ===\n\n";
