<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:04
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

$app->get('/full-flow', function () use ($guard) {
    $ip = '198.51.100.44';
    $subject = 'slim-flow@example.com';

    echo "===== Slim Full Flow =====\n";

    $attempt = LoginAttemptDTO::now($ip, $subject, 60);

    for ($i = 1; $i <= 3; $i++) {
        $guard->recordFailure($attempt);
        echo "Failure {$i}\n";
    }

    if ($guard->isBlocked($ip, $subject)) {
        echo "User blocked\n";
    } else {
        echo "User not blocked\n";
    }

    $block = new SecurityBlockDTO(
        ip       : $ip,
        subject  : $subject,
        type     : BlockTypeEnum::MANUAL,
        expiresAt: time() + 120,
        createdAt: time()
    );

    $guard->block($block);

    echo "Manual block applied\n";

    $guard->unblock($ip, $subject);

    echo "Block removed\n";

    $guard->resetAttempts($ip, $subject);

    echo "Attempts reset\n";

    $guard->cleanup();

    echo "Cleanup done\n";
});

$app->run();
