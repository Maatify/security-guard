<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Service;

use Maatify\DataAdapters\Contracts\AdapterInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SecurityGuardServiceEventTest extends TestCase
{
    private SecurityGuardService $service;
    /** @var EventDispatcherInterface&MockObject */
    private EventDispatcherInterface $dispatcher;
    /** @var AdapterInterface&MockObject */
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $strategy = new DefaultIdentifierStrategy();

        $this->service = new SecurityGuardService($this->adapter, $strategy);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->service->setEventDispatcher($this->dispatcher);
    }

    public function testRecordFailureDispatchesEvent(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), []);

        // The adapter call is mocked to avoid side effects
        $this->adapter->method('incr')->willReturn(1);
        $this->adapter->method('ttl')->willReturn(60);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SecurityEventDTO $event) {
                return (string)$event->action === 'login_attempt';
            }));

        $this->service->recordFailure($dto);
    }

    public function testBlockDispatchesEvent(): void
    {
        $dto = new SecurityBlockDTO(
            '1.2.3.4',
            'spammer',
            BlockTypeEnum::MANUAL,
            time() + 3600,
            time()
        );

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SecurityEventDTO $event) {
                return (string)$event->action === 'block_created'
                    && $event->ip === '1.2.3.4';
            }));

        $this->service->block($dto);
    }

    public function testUnblockDispatchesEvent(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SecurityEventDTO $event) {
                return (string)$event->action === 'block_removed'
                    && $event->ip === '1.2.3.4';
            }));

        $this->service->unblock('1.2.3.4', 'spammer');
    }

    public function testCleanupDispatchesEvent(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SecurityEventDTO $event) {
                return (string)$event->action === 'cleanup';
            }));

        $this->service->cleanup();
    }
}
