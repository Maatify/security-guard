<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:19
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

$guard = require __DIR__ . '/bootstrap.php';

$ip = '8.8.8.8';
$subject = 'login';

$block = new SecurityBlockDTO(
    ip       : $ip,
    subject  : $subject,
    type     : BlockTypeEnum::MANUAL,
    expiresAt: time() + 600,
    createdAt: time()
);

$guard->block($block);

echo "✔ Manually blocked user.\n";

sleep(1);

if ($guard->isBlocked($ip, $subject)) {
    echo "User still blocked.\n";
}

$guard->unblock($ip, $subject);
echo "✔ User unblocked.\n";
