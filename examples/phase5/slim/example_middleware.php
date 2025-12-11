<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 09:32
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * Phase 5 – Slim Example: SECURITY MIDDLEWARE (STRICT)
 *
 * Demonstrates:
 *  - Blocking login routes when user is already blocked
 *  - Intercepting requests BEFORE reaching handlers
 *  - Using only Phase 5: handleAttempt(), isBlocked(), getRemainingBlockSeconds()
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

use Slim\Factory\AppFactory;

use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$app = require __DIR__ . '/bootstrap.php';

// -------------------------------------------------------------
// SECURITY MIDDLEWARE (STRICT)
// -------------------------------------------------------------
final class SecurityMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SecurityGuardService $guard
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // ---------------------------------------------------------
        // 1) Determine subject (login username or route identifier)
        // ---------------------------------------------------------
        $subject = $request->getParsedBody()['subject'] ?? 'login';

        // ---------------------------------------------------------
        // 2) Determine IP
        // ---------------------------------------------------------
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';

        // ---------------------------------------------------------
        // 3) Check if already blocked
        // ---------------------------------------------------------
        if ($this->guard->isBlocked($ip, $subject)) {
            $rem = $this->guard->getRemainingBlockSeconds($ip, $subject);

            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'status'    => 'blocked',
                'ip'        => $ip,
                'subject'   => $subject,
                'remaining' => $rem,
            ]));

            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(429);
        }

        // ---------------------------------------------------------
        // Allow request to proceed
        // ---------------------------------------------------------
        return $handler->handle($request);
    }
}

// -------------------------------------------------------------
// REGISTER MIDDLEWARE IN SLIM
// -------------------------------------------------------------
$app->add(function ($request, $handler) use ($app) {
    $guard = $app->getContainer()->get(SecurityGuardService::class);

    return (new SecurityMiddleware($guard))->process($request, $handler);
});

// -------------------------------------------------------------
// SAMPLE LOGIN ROUTE (STRICT)
// -------------------------------------------------------------
$app->post('/login', function (ServerRequestInterface $request) use ($app) {
    /** @var SecurityGuardService $guard */
    $guard = $app->getContainer()->get(SecurityGuardService::class);
    $body = $request->getParsedBody();
    $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    $subject = $body['subject'] ?? 'login';

    // Build attempt DTO
    $dto = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : $request->getHeaderLine('User-Agent'),
        context   : ['route' => '/login']
    );

    // Simulate FAILURE for testing
    $result = $guard->handleAttempt($dto, false);

    $response = new \Slim\Psr7\Response();
    $response->getBody()->write(json_encode([
        'status'       => 'failed',
        'failureCount' => $result
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

// -------------------------------------------------------------
// READY
// -------------------------------------------------------------
echo "\n=== Slim Phase 5 — Security Middleware Example Loaded (STRICT) ===\n";

return $app;
