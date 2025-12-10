<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Event\Dispatcher;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class PsrLoggerDispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $dispatcher = new PsrLoggerDispatcher($logger);

        $event = new SecurityEventDTO(
            'id',
            SecurityAction::loginFailure(),
            SecurityPlatform::web(),
            time(),
            '127.0.0.1',
            'user',
            null,
            null,
            ['foo' => 'bar']
        );

        $logger->expects($this->once())
            ->method('info')
            ->with(
                'security_event',
                $this->callback(function ($context) use ($event) {
                    return $context === $event->jsonSerialize();
                })
            );

        $dispatcher->dispatch($event);
    }

    public function testDispatchSwallowsExceptions(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('info')->willThrowException(new RuntimeException('Log error'));

        $dispatcher = new PsrLoggerDispatcher($logger);

        $event = new SecurityEventDTO(
            'id',
            SecurityAction::loginFailure(),
            SecurityPlatform::web(),
            time(),
            '127.0.0.1',
            'user'
        );

        // Should not throw
        $dispatcher->dispatch($event);
        $this->assertTrue(true);
    }
}
