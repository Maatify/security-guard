<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Drivers\Support\RealRedisAdapter;
use PHPUnit\Framework\TestCase;

class Phase3G12RealRedisResetTest extends TestCase
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
            keyPrefix: 'phase12_reset:',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $config = new SecurityConfig($configDTO);
        $strategy = new DefaultIdentifierStrategy($config);

        $this->driver = new RedisSecurityGuard($this->adapter, $strategy);
    }

    public function testRedisIntegrationResetAttempts(): void
    {
        $driver = $this->driver;
        $this->assertNotNull($driver);

        $attempt = new LoginAttemptDTO(
            ip: '127.0.0.1',
            subject: 'user_reset',
            occurredAt: time(),
            resetAfter: 60,
            userAgent: 'test-agent',
            context: []
        );

        // Record some failures
        $driver->recordFailure($attempt);
        $count = $driver->recordFailure($attempt);
        $this->assertSame(2, $count);

        // Reset
        $driver->resetAttempts('127.0.0.1', 'user_reset');

        // Next failure should be 1
        $newCount = $driver->recordFailure($attempt);
        $this->assertSame(1, $newCount);
    }
}
