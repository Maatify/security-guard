<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:14
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$ip = '198.51.100.44';
$subject = 'laravel-flow@example.com';

echo "=== Laravel Full Flow ===\n";

// failures
$attempt = LoginAttemptDTO::now($ip, $subject, 60);
for ($i = 1; $i <= 3; $i++) {
    echo "Failure {$i}: " . $guard->recordFailure($attempt) . "\n";
}

// block
$block = new SecurityBlockDTO(
    ip       : $ip,
    subject  : $subject,
    type     : BlockTypeEnum::MANUAL,
    expiresAt: time() + 120,
    createdAt: time()
);
$guard->block($block);

echo "Blocked\n";

// unblock
$guard->unblock($ip, $subject);
echo "Unblocked\n";

// reset
$guard->resetAttempts($ip, $subject);
echo "Reset\n";

// cleanup
$guard->cleanup();
echo "Cleanup Done\n";
