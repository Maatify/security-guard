<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Drivers\Support\RealRedisAdapter;
use PHPUnit\Framework\TestCase;

class Phase12RealRedisTest extends TestCase
{
    private ?RealRedisAdapter $adapter = null;
    private ?RedisSecurityGuard $driver = null;

    protected function setUp(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded');
        }

        $this->adapter = new RealRedisAdapter();
        if (!$this->adapter->isConnected()) {
            $this->markTestSkipped('Redis connection failed');
        }

        /** @var \Redis $redis */
        $redis = $this->adapter->getDriver();
        $redis->flushAll();

        $configDTO = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'phase12:',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $config = new SecurityConfig($configDTO);
        $strategy = new DefaultIdentifierStrategy($config);

        $this->driver = new RedisSecurityGuard($this->adapter, $strategy);
    }

    public function testRedisIntegrationRecordFailure(): void
    {
        $driver = $this->driver;
        $this->assertNotNull($driver);

        $attempt = new LoginAttemptDTO(
            ip: '127.0.0.1',
            subject: 'user_fail',
            occurredAt: time(),
            resetAfter: 10,
            userAgent: 'test-agent',
            context: []
        );

        $count = $driver->recordFailure($attempt);
        $this->assertSame(1, $count);

        $count = $driver->recordFailure($attempt);
        $this->assertSame(2, $count);
    }

    public function testRedisIntegrationBlock(): void
    {
        $driver = $this->driver;
        $this->assertNotNull($driver);

        $block = new SecurityBlockDTO(
            ip: '127.0.0.1',
            subject: 'user_block',
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 5,
            createdAt: time()
        );

        $driver->block($block);

        $this->assertTrue($driver->isBlocked('127.0.0.1', 'user_block'));

        $retrieved = $driver->getActiveBlock('127.0.0.1', 'user_block');
        $this->assertNotNull($retrieved);
        $this->assertSame('user_block', $retrieved->subject);
    }

    public function testRedisIntegrationUnblock(): void
    {
        $driver = $this->driver;
        $this->assertNotNull($driver);

        $block = new SecurityBlockDTO(
            ip: '127.0.0.1',
            subject: 'user_unblock',
            type: BlockTypeEnum::MANUAL,
            expiresAt: time() + 60,
            createdAt: time()
        );

        $driver->block($block);
        $this->assertTrue($driver->isBlocked('127.0.0.1', 'user_unblock'));

        $driver->unblock('127.0.0.1', 'user_unblock');
        $this->assertFalse($driver->isBlocked('127.0.0.1', 'user_unblock'));
    }

    public function testRedisIntegrationCleanup(): void
    {
        $driver = $this->driver;
        $this->assertNotNull($driver);

        // Redis cleans up automatically, so this test mainly verifies no exception is thrown
        // and behavior is consistent.
        $driver->cleanup();
        $this->assertTrue(true);
    }
}
