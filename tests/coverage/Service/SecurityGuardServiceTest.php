<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Service;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use PHPUnit\Framework\TestCase;

class SecurityGuardServiceTest extends TestCase
{
    private function createService(
        SecurityGuardDriverInterface $driver,
        ?EventDispatcherInterface $dispatcher = null
    ): SecurityGuardService {
        $config = new SecurityConfig(new SecurityConfigDTO(
            60, 300, 3, IdentifierModeEnum::IP_ONLY, 'sg', true, 10, 2.0, 100
        ));

        return new SecurityGuardService($driver, $config, $dispatcher);
    }

    public function testRecordLoginAttemptSuccess(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $service = $this->createService($driver, $dispatcher);
        $platform = SecurityPlatform::web();

        // Expect driver reset on success
        $driver->expects($this->once())
            ->method('resetAttempts')
            ->with('sg:127.0.0.1');

        // Expect dispatch login_success
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) {
                return (string)$event->action === 'login_success';
            }));

        $service->recordLoginAttempt('127.0.0.1', 'user', true, $platform);
    }

    public function testRecordLoginAttemptFailureUnderLimit(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $service = $this->createService($driver, $dispatcher);
        $platform = SecurityPlatform::web();

        // Increment attempts, return 1 (under limit 3)
        $driver->expects($this->once())
            ->method('incrementAttempts')
            ->with('sg:127.0.0.1', 60)
            ->willReturn(1);

        // Should NOT block
        $driver->expects($this->never())->method('blockSubject');

        // Dispatch login_failure
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) {
                return (string)$event->action === 'login_failure';
            }));

        $service->recordLoginAttempt('127.0.0.1', 'user', false, $platform);
    }

    public function testRecordLoginAttemptFailureOverLimit(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $service = $this->createService($driver, $dispatcher);
        $platform = SecurityPlatform::web();

        // Increment attempts, return 3 (reaches limit)
        $driver->expects($this->once())
            ->method('incrementAttempts')
            ->willReturn(3);

        // Should block
        $driver->expects($this->once())
            ->method('blockSubject')
            ->with('sg:block:127.0.0.1', $this->anything()); // check DTO content if possible

        // Dispatch login_failure AND block_created
        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->with($this->logicalOr(
                $this->callback(fn($e) => (string)$e->action === 'login_failure'),
                $this->callback(fn($e) => (string)$e->action === 'block_created')
            ));

        $service->recordLoginAttempt('127.0.0.1', 'user', false, $platform);
    }

    public function testCheckBlockStatusNotBlocked(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $service = $this->createService($driver);

        $driver->expects($this->once())
            ->method('getBlock')
            ->with('sg:block:127.0.0.1')
            ->willReturn(null);

        $this->assertNull($service->checkBlockStatus('127.0.0.1', 'user'));
    }

    public function testCheckBlockStatusBlocked(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $service = $this->createService($driver);

        $block = new SecurityBlockDTO('127.0.0.1', 'user', BlockTypeEnum::AUTO, time() + 100, time());
        $driver->expects($this->once())
            ->method('getBlock')
            ->willReturn($block);

        $this->assertSame($block, $service->checkBlockStatus('127.0.0.1', 'user'));
    }

    public function testManualBlock(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $service = $this->createService($driver, $dispatcher);

        $driver->expects($this->once())->method('blockSubject');
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($e) => (string)$e->action === 'block_created'));

        $service->manualBlock('127.0.0.1', 'user', 600);
    }

    public function testRemoveBlock(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $service = $this->createService($driver, $dispatcher);

        $driver->expects($this->once())
            ->method('removeBlock')
            ->with('sg:block:127.0.0.1');

        $driver->expects($this->once())
            ->method('resetAttempts')
            ->with('sg:127.0.0.1');

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($e) => (string)$e->action === 'block_removed'));

        $service->removeBlock('127.0.0.1', 'user');
    }

    public function testCleanup(): void
    {
        $driver = $this->createMock(SecurityGuardDriverInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $service = $this->createService($driver, $dispatcher);

        $driver->expects($this->once())->method('cleanup')->willReturn(5);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($e) => (string)$e->action === 'cleanup'));

        $service->cleanup();
    }
}
