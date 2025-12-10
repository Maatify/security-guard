<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Dispatcher;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Dispatcher\NullDispatcher;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;

class NullDispatcherTest extends TestCase
{
    public function testDispatchDoesNotThrow(): void
    {
        $dispatcher = new NullDispatcher();
        $event = new SecurityEventDTO(
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

        $dispatcher->dispatch($event);
        $this->addToAssertionCount(1); // Ensure test is not marked risky
    }
}
