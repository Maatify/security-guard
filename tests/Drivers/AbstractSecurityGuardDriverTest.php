<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:08
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PHPUnit\Framework\TestCase;

final class AbstractSecurityGuardDriverTest extends TestCase
{
    private FakeSecurityGuardDriver $driver;

    protected function setUp(): void
    {
        $adapter = new FakeAdapter();
        $strategy = new FakeIdentifierStrategy();

        $this->driver = new FakeSecurityGuardDriver(
            $adapter,
            $strategy
        );
    }

    public function testEncodeDecodeBlock(): void
    {
        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'user',
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 100,
            createdAt: time()
        );

        $ref = new \ReflectionClass($this->driver);

        $encode = $ref->getMethod('encodeBlock')->invoke($this->driver, $block);
        $decode = $ref->getMethod('decodeBlock')->invoke($this->driver, $encode);

        $this->assertInstanceOf(SecurityBlockDTO::class, $decode);
        $this->assertSame('user', $decode->subject);
    }

    public function testRecordFailure(): void
    {
        $dto = new LoginAttemptDTO(
            ip        : '1.1.1.1',
            subject   : 'user',
            occurredAt: time(),
            resetAfter: 60,
            userAgent : null,
            context   : []
        );

        $this->assertSame(1, $this->driver->recordFailure($dto));
        $this->assertSame(2, $this->driver->recordFailure($dto));
    }

    public function testBlockAndIsBlocked(): void
    {
        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'user',
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 10,
            createdAt: time()
        );

        $this->driver->block($block);

        $this->assertTrue(
            $this->driver->isBlocked('1.1.1.1', 'user')
        );
    }

    public function testRemainingSeconds(): void
    {
        $expires = time() + 5;

        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'user',
            type     : BlockTypeEnum::AUTO,
            expiresAt: $expires,
            createdAt: time()
        );

        $this->driver->block($block);

        $remain = $this->driver->getRemainingBlockSeconds('1.1.1.1', 'user');

        $this->assertNotNull($remain);
        $this->assertTrue($remain <= 5 && $remain > 0);
    }

    public function testUnblock(): void
    {
        $block = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'user',
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 10,
            createdAt: time()
        );

        $this->driver->block($block);
        $this->assertTrue($this->driver->isBlocked('1.1.1.1', 'user'));

        $this->driver->unblock('1.1.1.1', 'user');
        $this->assertFalse($this->driver->isBlocked('1.1.1.1', 'user'));
    }

    public function testCleanup(): void
    {
        $expired = new SecurityBlockDTO(
            ip       : '1.1.1.1',
            subject  : 'user',
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() - 1,
            createdAt: time()
        );

        $this->driver->block($expired);
        $this->driver->cleanup();

        $this->assertFalse(
            $this->driver->isBlocked('1.1.1.1', 'user')
        );
    }

    public function testStats(): void
    {
        $stats = $this->driver->getStats();

        $this->assertArrayHasKey('failures', $stats);
        $this->assertArrayHasKey('blocks', $stats);
    }

    public function testAttemptsResetAfterTTL(): void
    {
        $dto = new LoginAttemptDTO(
            ip        : '5.5.5.5',
            subject   : 'user',
            occurredAt: time(),
            resetAfter: 2,
            userAgent : null,
            context   : []
        );

        $this->assertSame(1, $this->driver->recordFailure($dto));
        $this->assertSame(2, $this->driver->recordFailure($dto));

        sleep(3);

        $this->assertSame(1, $this->driver->recordFailure($dto));
    }
}
