<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class SecurityGuardServiceTest extends TestCase
{
    private SecurityGuardService $service;

    /** @var Client&MockObject */
    private Client $predis;

    private MockObject $dispatcher;

    protected function setUp(): void
    {
        // Require FakePredisClient if not already loaded (safe guard)
        if (!class_exists(Client::class, false)) {
            // @codeCoverageIgnoreStart
            require_once __DIR__ . '/../Fake/FakePredisClient.php';
            // @codeCoverageIgnoreEnd
        }

        // Mock Predis Client
        // We only mock methods called by RedisClientProxy
        /** @var Client&MockObject $predisMock */
        $predisMock = $this->getMockBuilder(Client::class)
            ->onlyMethods(['incr', 'expire', 'hGetAll', 'hMSet', 'del', 'ttl', 'info'])
            ->getMock();
        $this->predis = $predisMock;

        // Create Adapter returning the mock
        $adapter = new class($this->predis) implements AdapterInterface {
            public function __construct(private Client $predis) {}
            public function connect(): void {}
            public function disconnect(): void {}
            public function isConnected(): bool { return true; }
            public function healthCheck(): bool { return true; }
            public function getDriver(): Client { return $this->predis; }
            public function getConnection(): Client { return $this->predis; }
        };

        // Service setup
        $this->service = new SecurityGuardService($adapter, new FakeIdentifierStrategy());

        // Config
        $this->service->setConfig(new SecurityConfig(new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 3,
            identifierMode: IdentifierModeEnum::IDENTIFIER_ONLY,
            keyPrefix: 'test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        )));

        // Dispatcher
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->service->setEventDispatcher($this->dispatcher);
    }

    public function testGetConfigReturnsCurrentConfig(): void
    {
        $config = $this->service->getConfig();
        $this->assertInstanceOf(SecurityConfig::class, $config);
        $this->assertSame(60, $config->windowSeconds());
    }

    public function testHandleAttemptSuccessResetsAttempts(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60);

        // Expect check for blocked (hGetAll returns empty -> not blocked)
        $this->predis->expects($this->once())
            ->method('hGetAll')
            ->willReturn([]);

        // Expect del to be called
        $this->predis->expects($this->once())
            ->method('del');

        $result = $this->service->handleAttempt($dto, true);
        $this->assertNull($result);
    }

    public function testHandleAttemptBlockedReturnsRemainingSeconds(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60);

        // Expect check for blocked -> returns block data
        $expiresAt = time() + 100;
        $this->predis->expects($this->any())
            ->method('hGetAll')
            ->willReturn([
                'ip' => '127.0.0.1',
                'subject' => 'user',
                'expires_at' => (string)$expiresAt,
                'created_at' => (string)(time() - 10),
                'type' => 'auto'
            ]);

        // Also ttl might be called by getRemainingBlockSeconds
        $this->predis->expects($this->any())
            ->method('ttl')
            ->willReturn(100);

        $result = $this->service->handleAttempt($dto, false); // Success/Fail doesn't matter if blocked
        $this->assertEquals(100, $result);
    }

    public function testHandleAttemptFailureIncrementsCount(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60);

        // Not blocked
        $this->predis->expects($this->once())
            ->method('hGetAll')
            ->willReturn([]);

        // Record failure
        $this->predis->expects($this->once())
            ->method('incr')
            ->willReturn(1);

        $this->predis->expects($this->once())
            ->method('expire');

        // Dispatch failure event
        $this->dispatcher->expects($this->once())
            ->method('dispatch');

        $result = $this->service->handleAttempt($dto, false);
        $this->assertSame(1, $result);
    }

    public function testHandleAttemptFailureThresholdTriggersBlock(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60);

        // Not blocked
        $this->predis->expects($this->once())
            ->method('hGetAll')
            ->willReturn([]);

        // Record failure -> returns 3 (max failures)
        $this->predis->expects($this->once())
            ->method('incr')
            ->willReturn(3);

        // Block
        $this->predis->expects($this->once())
            ->method('hMSet');

        $this->predis->expects($this->atLeast(1))
            ->method('expire'); // one for failure record, one for block

        // Dispatch 2 events: login_attempt and block_created
        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $result = $this->service->handleAttempt($dto, false);
        $this->assertSame(3, $result);
    }

    public function testRecordFailure(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60);

        $this->predis->expects($this->once())->method('incr')->willReturn(5);
        $this->dispatcher->expects($this->once())->method('dispatch');

        $this->assertSame(5, $this->service->recordFailure($dto));
    }

    public function testResetAttempts(): void
    {
        $this->predis->expects($this->once())->method('del');
        $this->service->resetAttempts('1.2.3.4', 'u');
    }

    public function testGetActiveBlock(): void
    {
        $this->predis->expects($this->once())
            ->method('hGetAll')
            ->willReturn([
                'ip' => '1.2.3.4',
                'subject' => 'u',
                'expires_at' => '0',
                'created_at' => '123',
                'type' => 'manual'
            ]);

        $block = $this->service->getActiveBlock('1.2.3.4', 'u');
        $this->assertInstanceOf(SecurityBlockDTO::class, $block);
    }

    public function testIsBlocked(): void
    {
        $this->predis->expects($this->once())
            ->method('hGetAll')
            ->willReturn([]); // Not blocked

        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'u'));
    }

    public function testBlock(): void
    {
        $block = new SecurityBlockDTO('1.1.1.1', 'u', BlockTypeEnum::MANUAL, 0, time());

        $this->predis->expects($this->once())->method('hMSet');
        $this->dispatcher->expects($this->once())->method('dispatch');

        $this->service->block($block);
    }

    public function testUnblock(): void
    {
        $this->predis->expects($this->once())->method('del');
        $this->dispatcher->expects($this->once())->method('dispatch');

        $this->service->unblock('1.1.1.1', 'u');
    }

    public function testCleanup(): void
    {
        // Redis driver cleanup does nothing, but service dispatches event
        $this->dispatcher->expects($this->once())->method('dispatch');
        $this->service->cleanup();
    }

    public function testGetStats(): void
    {
        $this->predis->expects($this->once())->method('info')->willReturn(['fake' => true]);

        $stats = $this->service->getStats();
        $this->assertArrayHasKey('redis_info', $stats);
        /** @var array<string, bool> $redisInfo */
        $redisInfo = $stats['redis_info'];
        $this->assertTrue($redisInfo['fake']);
    }

    public function testHandleEvent(): void
    {
        $event = SecurityEventFactory::cleanup(SecurityPlatform::custom('test')); // just a random event
        $this->dispatcher->expects($this->once())->method('dispatch')->with($event);

        $this->service->handleEvent($event);
    }
}
