<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
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

        $dto = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 60,
            maxFailures: 3,
            identifierMode: IdentifierModeEnum::IP_AND_SUBJECT,
            keyPrefix: 'event_test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($dto));
    }

    public function testAttemptThenBlockThenCleanup(): void
    {
        $events = [];
        $this->dispatcher->addClosure(function (SecurityEventDTO $e) use (&$events) {
            $events[] = (string)$e->action;
        });

        $dto = new LoginAttemptDTO('127.0.0.1', 'evt', time(), 300, null, []);

        // 1. Fail (emits login_failure/attempt)
        $this->service->handleAttempt($dto, false);

        // 2. Cleanup (emits cleanup)
        $this->service->cleanup();

        $this->assertCount(2, $events);
        $this->assertContains('cleanup', $events);
        $this->assertSame('cleanup', end($events));
    }
}
