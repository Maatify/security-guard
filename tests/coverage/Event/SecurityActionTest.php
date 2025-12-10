<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Event;

use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use Maatify\SecurityGuard\Event\SecurityAction;
use PHPUnit\Framework\TestCase;

class SecurityActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $action = new SecurityAction('custom_action');
        $this->assertSame('custom_action', $action->value);
    }

    public function testFromEnum(): void
    {
        $action = SecurityAction::fromEnum(SecurityActionEnum::LOGIN_FAILURE);
        $this->assertSame('login_failure', $action->value);
    }

    public function testCustom(): void
    {
        $action = SecurityAction::custom('my_event');
        $this->assertSame('my_event', (string)$action);
    }

    public function testToString(): void
    {
        $action = new SecurityAction('test');
        $this->assertSame('test', (string)$action);
    }
}
