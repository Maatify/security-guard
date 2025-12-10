<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-11 01:20
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;

$guard = require __DIR__ . '/bootstrap.php';

$adminConfig = new SecurityConfig(
    new SecurityConfigDTO(
        windowSeconds: 30,
        blockSeconds: 900,
        maxFailures: 3,
        identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
        keyPrefix: "admin:",
        backoffEnabled: true,
        initialBackoffSeconds: 120,
        backoffMultiplier: 2.0,
        maxBackoffSeconds: 1800
    )
);

$guard->setConfig($adminConfig);

echo "Admin security config applied.\n";


