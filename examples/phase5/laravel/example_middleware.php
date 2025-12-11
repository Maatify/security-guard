<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:15
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/bootstrap.php';

echo "\n=== Laravel Phase 5 — Middleware Simulation (STRICT) ===\n\n";

/**
 * --------------------------------------------------------------
 *  Simple Laravel-style Middleware (NOT real Laravel middleware)
 * --------------------------------------------------------------
 */
final class SecurityGuardMiddleware
{
    public function __construct(
        private readonly \Maatify\SecurityGuard\Service\SecurityGuardService $guard
    )
    {
    }

    /**
     * Simulate handling a request with:
     * - route
     * - IP
     * - subject
     * - success/failure
     *
     * @return array<string, mixed>
     */
    public function handle(string $route, string $ip, string $subject, bool $success): array
    {
        // Already blocked?
        if ($this->guard->isBlocked($ip, $subject)) {
            $remaining = $this->guard->getRemainingBlockSeconds($ip, $subject);

            return [
                'blocked'   => true,
                'remaining' => $remaining,
                'message'   => "🚫 Access denied from middleware"
            ];
        }

        // Simulate LoginAttemptDTO
        $attempt = LoginAttemptDTO::now(
            ip        : $ip,
            subject   : $subject,
            resetAfter: $this->guard->getConfig()->windowSeconds(),
            userAgent : 'CLI',
            context   : ['route' => $route]
        );

        // Process
        $result = $guardResult = $this->guard->handleAttempt($attempt, $success);

        return [
            'blocked' => false,
            'success' => $success,
            'result'  => $result,
            'message' => $success
                ? "✔ login success — middleware allowed request"
                : "✖ login failure — tracked by middleware",
        ];
    }
}

$mw = new SecurityGuardMiddleware($guard);

// ------------------------------------------------------------------
// Scenario 1 — Fail 3 times
// ------------------------------------------------------------------
echo "➡ Scenario #1 — multiple failures\n";
for ($i = 1; $i <= 3; $i++) {
    $res = $mw->handle('/login', '192.168.1.10', 'user@example.com', false);
    print_r($res);
}

// ------------------------------------------------------------------
// Scenario 2 — Success resets counters
// ------------------------------------------------------------------
echo "\n➡ Scenario #2 — Success resets attempts\n";
$res = $mw->handle('/login', '192.168.1.10', 'user@example.com', true);
print_r($res);

// ------------------------------------------------------------------
// Scenario 3 — Enough failures → block
// ------------------------------------------------------------------
echo "\n➡ Scenario #3 — Trigger auto-block\n";
for ($i = 1; $i <= 6; $i++) {
    $res = $mw->handle('/login', '10.0.0.77', 'admin@example.com', false);

    print_r($res);

    if ($guard->isBlocked('10.0.0.77', 'admin@example.com')) {
        echo "🚫 User is now BLOCKED\n";
        break;
    }
}

echo "\n=== END MIDDLEWARE EXAMPLE ===\n\n";
