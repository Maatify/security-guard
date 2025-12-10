<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:08
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Illuminate\Http\Request;
use Closure;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

/** @var SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

class SecurityGuardMiddleware
{
    public function __construct(private SecurityGuardService $guard)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $subject = $request->input('email', 'unknown');

        if ($this->guard->isBlocked($ip, $subject)) {
            return response()->json([
                'error'     => 'Too many attempts — access blocked.',
                'remaining' => $this->guard->getRemainingBlockSeconds($ip, $subject)
            ], 429);
        }

        return $next($request);
    }
}

// controller simulation
$controller = function (Request $req) use ($guard) {
    $attempt = LoginAttemptDTO::now(
        ip        : $req->ip(),
        subject   : $req->input('email'),
        resetAfter: 60
    );

    $count = $guard->recordFailure($attempt);

    return response()->json(['failures' => $count]);
};

$request = Request::create('/login', 'POST', ['email' => 'middleware@test.com']);

$middleware = new SecurityGuardMiddleware($guard);

$response = $middleware->handle($request, fn($req) => $controller($req));

echo $response->getContent();
