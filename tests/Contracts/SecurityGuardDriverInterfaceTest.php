<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 17:25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Contracts;

use Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SecurityGuardDriverInterfaceTest extends TestCase
{
    public function testInterfaceMethodsExist(): void
    {
        $ref = new ReflectionClass(SecurityGuardDriverInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $ref->getMethods());

        $expected = [
            'recordFailure',
            'resetAttempts',
            'getActiveBlock',
            'isBlocked',
            'getRemainingBlockSeconds',
            'block',
            'unblock',
            'cleanup',
            'getStats',
        ];

        foreach ($expected as $method) {
            $this->assertContains($method, $methods);
        }
    }
}
