<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Dispatcher;

use Exception;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Contracts\EventListenerInterface;
use Maatify\SecurityGuard\Event\Dispatcher\SyncDispatcher;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;

class SyncDispatcherTest extends TestCase
{
    private SyncDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new SyncDispatcher();
    }

    private function createEvent(): SecurityEventDTO
    {
        return new SecurityEventDTO(
            'id',
            SecurityAction::custom('test'),
            SecurityPlatform::custom('test'),
            time(),
            '127.0.0.1',
            'subj',
            null,
            null,
            []
        );
    }

    public function testClosureListenerReceivesEvent(): void
    {
        $received = false;
        $this->dispatcher->addClosure(function (SecurityEventDTO $event) use (&$received) {
            $received = true;
            $this->assertSame('test', (string)$event->action);
        });

        $this->dispatcher->dispatch($this->createEvent());
        $this->assertTrue($received);
    }

    public function testObjectListenerReceivesEvent(): void
    {
        $listener = new class () implements EventListenerInterface {
            public bool $received = false;
            public function handle(SecurityEventDTO $event): void
            {
                $this->received = true;
            }
        };

        $this->dispatcher->addListener($listener);
        $this->dispatcher->dispatch($this->createEvent());

        $this->assertTrue($listener->received);
    }

    public function testExceptionInClosureIsSwallowed(): void
    {
        $this->dispatcher->addClosure(function (SecurityEventDTO $event) {
            throw new Exception('Boom');
        });

        // Should not throw
        $this->dispatcher->dispatch($this->createEvent());
        $this->addToAssertionCount(1);
    }

    public function testExceptionInObjectListenerIsSwallowed(): void
    {
        $listener = new class () implements EventListenerInterface {
            public function handle(SecurityEventDTO $event): void
            {
                throw new Exception('Boom');
            }
        };

        $this->dispatcher->addListener($listener);
        $this->dispatcher->dispatch($this->createEvent());
        $this->addToAssertionCount(1);
    }
}
