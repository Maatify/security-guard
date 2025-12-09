<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:02
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers\MySQL;

use Maatify\SecurityGuard\Drivers\MySQL\Contracts\MySQLDriverInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MySQLDriverInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(MySQLDriverInterface::class));
    }

    public function testMethodSignatures(): void
    {
        $ref = new ReflectionClass(MySQLDriverInterface::class);

        $expected = [
            'doRecordFailure',
            'doResetAttempts',
            'doGetActiveBlock',
            'doGetRemainingBlockSeconds',
            'doBlock',
            'doUnblock',
            'doCleanup',
            'doGetStats',
        ];

        foreach ($expected as $method) {
            $this->assertTrue(
                $ref->hasMethod($method),
                "Method $method must exist in MySQLDriverInterface."
            );
        }
    }
}
