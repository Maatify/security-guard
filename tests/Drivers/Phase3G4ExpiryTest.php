<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Drivers\Support\RealRedisAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use PHPUnit\Framework\TestCase;

class Phase3G4ExpiryTest extends TestCase
{
    public function testBlockExpiresAfterTTL_Fake(): void
    {
        $adapter = new FakeAdapter();
        $strategy = new FakeIdentifierStrategy();
        $driver = new FakeSecurityGuardDriver($adapter, $strategy);

        $block = new SecurityBlockDTO(
            ip: '10.0.0.1',
            subject: 'user1',
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 2, // 2 seconds TTL
            createdAt: time()
        );

        $driver->block($block);

        $this->assertTrue($driver->isBlocked('10.0.0.1', 'user1'));

        sleep(3);

        $this->assertFalse($driver->isBlocked('10.0.0.1', 'user1'), 'Block should have expired in Fake driver');
    }

    public function testBlockExpiresAfterTTL_Redis(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded');
        }

        $adapter = new RealRedisAdapter();
        if (!$adapter->isConnected()) {
             $this->markTestSkipped('Redis not available');
        }

        // Flush DB to ensure clean state
        /** @var \Redis $redis */
        $redis = $adapter->getDriver();
        $redis->flushAll();

        $configDTO = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'test_phase4:',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $config = new SecurityConfig($configDTO);
        $strategy = new DefaultIdentifierStrategy($config);

        $driver = new RedisSecurityGuard($adapter, $strategy);

        $block = new SecurityBlockDTO(
            ip: '10.0.0.2',
            subject: 'user2',
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 2,
            createdAt: time()
        );

        $driver->block($block);

        $this->assertTrue($driver->isBlocked('10.0.0.2', 'user2'));

        sleep(3);

        $this->assertFalse($driver->isBlocked('10.0.0.2', 'user2'), 'Block should have expired in Redis driver');
    }
}
