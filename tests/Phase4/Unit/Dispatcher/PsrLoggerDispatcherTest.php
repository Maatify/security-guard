<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Dispatcher;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\PsrLoggerDispatcher;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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

    public function testLogsEventWithPsrLogger(): void
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
            ->with(
                'security_event',
                $this->callback(function (array $context) {
                    return $context['action'] === 'log_me'
                        && $context['ip'] === '127.0.0.1'
                        && $context['context']['foo'] === 'bar';
                })
            );

        $this->dispatcher->dispatch($event);
    }
}
