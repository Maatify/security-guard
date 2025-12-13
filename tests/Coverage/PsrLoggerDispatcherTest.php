<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class PsrLoggerDispatcherTest extends TestCase
{
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;
    private PsrLoggerDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->dispatcher = new PsrLoggerDispatcher($this->logger);
    }

    public function testDispatchSilencesLoggerExceptions(): void
    {
        $event = new SecurityEventDTO(
            'event-id',
            SecurityAction::custom('log_me'),
            SecurityPlatform::custom('test'),
            time(),
            '127.0.0.1',
            'subj',
            null,
            null,
            ['foo' => 'bar']
        );

        $this->logger->expects($this->once())
            ->method('info')
            ->willThrowException(new RuntimeException('Logger failed'));

        $this->dispatcher->dispatch($event);

        // If we reach here without exception, the catch block works.
    }
}
