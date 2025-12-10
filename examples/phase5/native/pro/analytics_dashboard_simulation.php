<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:36
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

/**
 * PRO Example 5:
 * Analytics dashboard simulation using getStats() + live state.
 */

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$guard = require __DIR__ . '/../bootstrap.php';
$config = $guard->getConfig();

echo "\n===== ANALYTICS DASHBOARD SIMULATION =====\n\n";

$logins = [
    ['ip' => '10.1.1.1', 'subject' => 'mohamed'],
    ['ip' => '10.1.1.2', 'subject' => 'mohamed'],
    ['ip' => '10.1.1.3', 'subject' => 'guest'],
    ['ip' => '10.1.1.4', 'subject' => 'guest'],
];

foreach ($logins as $login) {
    $dto = LoginAttemptDTO::now(
        ip        : $login['ip'],
        subject   : $login['subject'],
        resetAfter: $config->windowSeconds(),
        context   : ['dashboard' => true]
    );

    $guard->handleAttempt($dto, false);
}

$stats = $guard->getStats();

echo "📊 TOTAL STATS\n";
echo json_encode($stats, JSON_PRETTY_PRINT) . "\n\n";

foreach ($logins as $login) {
    if ($guard->isBlocked($login['ip'], $login['subject'])) {
        echo "🚫 {$login['ip']} / {$login['subject']} is BLOCKED\n";
    } else {
        echo "✔ {$login['ip']} / {$login['subject']} is ACTIVE\n";
    }
}
