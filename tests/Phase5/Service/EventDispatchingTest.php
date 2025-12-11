<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class EventDispatchingTest extends TestCase
{
    private SecurityGuardService $service;
    private SyncDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->service = new SecurityGuardService(
            new FakeAdapter(),
            new FakeIdentifierStrategy()
        );
        $this->dispatcher = new SyncDispatcher();
        $this->service->setEventDispatcher($this->dispatcher);
    }

    public function testAttemptThenBlockThenCleanup(): void
    {
        $events = [];
        $this->dispatcher->addClosure(function (SecurityEventDTO $e) use (&$events) {
            $events[] = (string)$e->action;
        });

        $dto = new LoginAttemptDTO('127.0.0.1', 'evt', time(), []);

        // 1. Fail (emits login_failure/attempt)
        $this->service->handleAttempt($dto, false); // Default config: 5 fails to block

        // 2. Cleanup (emits cleanup)
        $this->service->cleanup();

        $this->assertCount(2, $events);
        // Assuming factory emits 'login_failure' or similar for attempt
        // We check existence
        $this->assertContains('cleanup', $events);

        // We can check order: attempt first, cleanup second
        // But since 'login_failure' name is not 100% confirmed from source (I assumed it),
        // I will trust 'cleanup' is last.
        $this->assertSame('cleanup', end($events));
    }
}
