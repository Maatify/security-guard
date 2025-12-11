<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 10:27
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 * @note        Phase 5 – PRO Scenario: Analytics Dashboard Simulation (Laravel Style, STRICT)
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

$guard = require __DIR__ . '/../bootstrap.php';

echo "\n=== Laravel PRO – Analytics Dashboard Simulation (STRICT) ===\n\n";

// ---------------------------------------------------------------------
// Attach event listener (prints events for demo)
// ---------------------------------------------------------------------
$guard->setEventDispatcher(new class {
    public function dispatch(SecurityEventDTO $e): void
    {
        echo "📡 EVENT: {$e->action->value} from {$e->platform->value}\n";
    }
});

// ---------------------------------------------------------------------
// Simulate different traffic types
// ---------------------------------------------------------------------
function simulate_login(
    SecurityGuardService $guard,
    string $ip,
    string $subject,
    bool $success,
    array $ctx = []
): void
{
    $attempt = LoginAttemptDTO::now(
        ip        : $ip,
        subject   : $subject,
        resetAfter: $guard->getConfig()->windowSeconds(),
        userAgent : "CLI",
        context   : $ctx
    );

    $guard->handleAttempt($attempt, $success);
}

// ---------------------------------------------------------------------
// TRAFFIC PATTERNS FOR ANALYTICS
// ---------------------------------------------------------------------

echo "➡ Simulating traffic...\n\n";

// • Normal users (healthy traffic)
simulate_login($guard, "100.20.10.1", "alice", true);
simulate_login($guard, "100.20.10.2", "bob", true);
simulate_login($guard, "100.20.10.3", "charlie", false);

// • Suspicious failed logins
for ($i = 0; $i < 4; $i++) {
    simulate_login($guard, "55.66.77.88", "suspicious_user", false, ['attempt' => $i + 1]);
}

// • API client requests
simulate_login($guard, "8.8.8.8", "api-client-1", false, ['route' => '/api/token']);
simulate_login($guard, "8.8.8.8", "api-client-1", true, ['route' => '/api/token']);

// • Admin login noise
simulate_login($guard, "10.0.0.20", "root_admin", false);
simulate_login($guard, "10.0.0.20", "root_admin", false);

// ---------------------------------------------------------------------
// GATHER ANALYTICS
// ---------------------------------------------------------------------

echo "\n➡ Fetching global stats...\n\n";

$stats = $guard->getStats();

print_r([
    'total_failures'  => $stats['failures'] ?? 0,
    'active_blocks'   => $stats['active_blocks'] ?? 0,
    'expired_blocks'  => $stats['expired_blocks'] ?? 0,
    'unique_subjects' => $stats['subjects'] ?? 0,
    'unique_ips'      => $stats['ips'] ?? 0,
]);

echo "\n=== END ANALYTICS DASHBOARD SIMULATION ===\n\n";
