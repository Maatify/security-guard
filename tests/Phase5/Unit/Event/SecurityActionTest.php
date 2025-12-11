<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Event;

use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use PHPUnit\Framework\TestCase;

class SecurityActionTest extends TestCase
{
    public function testFromEnum(): void
    {
        $action = SecurityAction::fromEnum(SecurityActionEnum::LOGIN_SUCCESS);
        $this->assertSame('login_success', (string)$action);
    }

    public function testCustom(): void
    {
        $action = SecurityAction::custom('my_custom_action');
        $this->assertSame('my_custom_action', (string)$action);
    }
}
