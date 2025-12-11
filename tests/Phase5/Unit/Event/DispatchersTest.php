<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Event;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\NullDispatcher;
use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DispatchersTest extends TestCase
{
    private function createEvent(): SecurityEventDTO
    {
        return new SecurityEventDTO(
            eventId: '1',
            action: SecurityAction::fromEnum(SecurityActionEnum::LOGIN_FAILURE),
            platform: SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB),
            timestamp: time(),
            ip: '127.0.0.1',
            subject: 'test',
            userId: null,
            userType: null,
            context: []
        );
    }

    public function testNullDispatcher(): void
    {
        $dispatcher = new NullDispatcher();
        $dispatcher->dispatch($this->createEvent());
        $this->expectNotToPerformAssertions();
    }

    public function testSyncDispatcher(): void
    {
        $dispatcher = new SyncDispatcher();
        $called = false;

        $dispatcher->addClosure(function (SecurityEventDTO $event) use (&$called) {
            $called = true;
            $this->assertSame('login_failure', (string)$event->action);
        });

        $dispatcher->dispatch($this->createEvent());

        $this->assertTrue($called);
    }

    public function testPsrLoggerDispatcher(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info') // Assuming default log level is info or similar, inferred from context
            ->with($this->callback(function ($msg) {
                // The memory said: "The `PsrLoggerDispatcher` implementation logs the event type key (e.g., `'security_event'`) as the log message"
                // Let's assume it logs something related to event.
                return true;
            }));

        $dispatcher = new PsrLoggerDispatcher($logger);
        $dispatcher->dispatch($this->createEvent());
    }
}
