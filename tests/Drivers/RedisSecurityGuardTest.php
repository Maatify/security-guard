<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:23
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

// Override ONLY for this test file
require_once __DIR__ . '/../Fake/FakePredisClient.php';

use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PHPUnit\Framework\TestCase;

class RedisSecurityGuardTest extends TestCase
{
    private RedisSecurityGuard $guard;

    protected function setUp(): void
    {
        $adapter = new FakeAdapter();

        $this->guard = new RedisSecurityGuard(
            $adapter,
            new FakeIdentifierStrategy()
        );
    }

    public function testRecordFailure(): void
    {
        $dto = new LoginAttemptDTO(
            ip        : '1.1.1.1',
            subject   : 'mohamed',
            occurredAt: time(),
            resetAfter: 60,
            userAgent : 'UA',
            context   : []
        );

        $this->assertSame(1, $this->guard->recordFailure($dto));
        $this->assertSame(2, $this->guard->recordFailure($dto));
    }

    public function testBlockAndGetActiveBlock(): void
    {
        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'x',
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 10,
            createdAt: time()
        );

        $this->guard->block($block);

        $active = $this->guard->getActiveBlock('1.1.1.1', 'x');

        $this->assertInstanceOf(SecurityBlockDTO::class, $active);
        $this->assertSame('x', $active->subject);
        $this->assertSame('1.1.1.1', $active->ip);
    }

    public function testUnblock(): void
    {
        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'y',
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 20,
            createdAt: time()
        );

        $this->guard->block($block);

        $this->assertNotNull($this->guard->getActiveBlock('1.1.1.1', 'y'));

        $this->guard->unblock('1.1.1.1', 'y');

        $this->assertNull($this->guard->getActiveBlock('1.1.1.1', 'y'));
    }

    public function testRemainingBlockSeconds(): void
    {
        $expires = time() + 7;

        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'z',
            type     : BlockTypeEnum::MANUAL,
            expiresAt: $expires,
            createdAt: time()
        );

        $this->guard->block($block);

        $ttl = $this->guard->getRemainingBlockSeconds('1.1.1.1', 'z');

        $this->assertTrue($ttl <= 7 && $ttl > 0);
    }

    public function testGetStats(): void
    {
        $stats = $this->guard->getStats();

        $this->assertArrayHasKey('driver', $stats);
        $this->assertSame('redis', $stats['driver']);
        $this->assertTrue($stats['connected']);
        $this->assertIsArray($stats['redis_info']);
    }
}
