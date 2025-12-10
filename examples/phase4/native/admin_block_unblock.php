<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:13
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

// ---------------------------------------------------------------------
// 🔒 Admin manually blocking an account
// ---------------------------------------------------------------------

$block = new SecurityBlockDTO(
    ip       : '8.8.8.8',
    subject  : 'malicious-user',
    type     : BlockTypeEnum::MANUAL,
    expiresAt: time() + 3600,
    createdAt: time()
);

$guard->block($block);

echo "Manual block created.\n";

// ---------------------------------------------------------------------
// 🔓 Remove the block
// ---------------------------------------------------------------------

$guard->unblock('8.8.8.8', 'malicious-user');

echo "Block removed.\n";
