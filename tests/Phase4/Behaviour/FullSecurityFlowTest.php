<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Behaviour;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use PHPUnit\Framework\TestCase;

class FullSecurityFlowTest extends TestCase
{
    private SecurityGuardService $service;
    private array $eventLog = [];

    protected function setUp(): void
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('incr')->willReturnOnConsecutiveCalls(1, 2, 3);
        $adapter->method('ttl')->willReturn(60);

        $this->service = new SecurityGuardService($adapter, new DefaultIdentifierStrategy());

        $dispatcher = new SyncDispatcher();
        $dispatcher->addClosure(function (SecurityEventDTO $event) {
            $this->eventLog[] = (string)$event->action;
        });

        $this->service->setEventDispatcher($dispatcher);
    }

    public function testFullSecurityFlow(): void
    {
        // 1. Record 3 failures
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60, null, []);
        $this->service->recordFailure($dto);
        $this->service->recordFailure($dto);
        $this->service->recordFailure($dto);

        $this->assertCount(3, $this->eventLog);
        $this->assertSame(['login_attempt', 'login_attempt', 'login_attempt'], $this->eventLog);

        // 2. Reset
        $this->service->resetAttempts('127.0.0.1', 'user');

        // 3. Cleanup
        $this->service->cleanup();
        $this->assertSame('cleanup', end($this->eventLog));
    }
}
