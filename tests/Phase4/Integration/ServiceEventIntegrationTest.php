<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Integration;

require_once __DIR__ . '/../../Fake/FakePredisClient.php';

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class ServiceEventIntegrationTest extends TestCase
{
    private SecurityGuardService $service;
    private SyncDispatcher $dispatcher;

    /** @var SecurityEventDTO[] */
    private array $events = [];

    protected function setUp(): void
    {
        $adapter = new FakeAdapter();
        $this->service = new SecurityGuardService($adapter, new FakeIdentifierStrategy());
        $this->dispatcher = new SyncDispatcher();

        $this->dispatcher->addClosure(function (SecurityEventDTO $event) {
            $this->events[] = $event;
        });

        $this->service->setEventDispatcher($this->dispatcher);
    }

    public function testEventsFiredInCorrectOrder(): void
    {
        // 1. Record Failure
        $this->service->recordFailure(new LoginAttemptDTO('1.1.1.1', 'user', time(), 60, null, []));
        $this->assertCount(1, $this->events);
        $this->assertSame('login_attempt', (string)$this->events[0]->action);

        // 2. Block
        $this->service->block(new SecurityBlockDTO(
            '1.1.1.1',
            'user',
            BlockTypeEnum::MANUAL,
            time() + 60,
            time()
        ));
        $this->assertCount(2, $this->events);
        $this->assertSame('block_created', (string)$this->events[1]->action);

        // 3. Unblock
        $this->service->unblock('1.1.1.1', 'user');
        $this->assertCount(3, $this->events);
        $this->assertSame('block_removed', (string)$this->events[2]->action);

        // 4. Cleanup
        $this->service->cleanup();
        $this->assertCount(4, $this->events);
        $this->assertSame('cleanup', (string)$this->events[3]->action);
    }

    public function testDispatcherReceivesActionAndPlatform(): void
    {
        $this->service->recordFailure(new LoginAttemptDTO('1.1.1.1', 'user', time(), 60, null, []));

        $event = $this->events[0];
        $this->assertSame('web', (string)$event->platform);
        $this->assertSame('login_attempt', (string)$event->action);
    }
}
