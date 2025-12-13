<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-14 00:50
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration;

use PHPUnit\Framework\TestCase;

abstract class BaseIntegrationTestCase extends TestCase
{
    protected function requireExtension(string $extension): void
    {
        if (! extension_loaded($extension)) {
            $this->markTestSkipped(
                sprintf('Required PHP extension "%s" is not loaded.', $extension)
            );
        }
    }

    protected function requireEnv(string $key): void
    {
        if (empty($_ENV[$key])) {
            $this->markTestSkipped(
                sprintf('Required environment variable "%s" is not configured.', $key)
            );
        }
    }
}