<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:11
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

// Manual block
$block = new SecurityBlockDTO(
    ip       : '8.8.8.8',
    subject  : 'laravel-malicious',
    type     : BlockTypeEnum::MANUAL,
    expiresAt: time() + 3600,
    createdAt: time()
);

$guard->block($block);

echo "Block created.\n";

// Unblock later
$guard->unblock('8.8.8.8', 'laravel-malicious');

echo "Block removed.\n";
