<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Event;

use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use Maatify\SecurityGuard\Event\SecurityAction;
use PHPUnit\Framework\TestCase;

class SecurityActionTest extends TestCase
{
    public function testFromEnumProducesCorrectValue(): void
    {
        $action = SecurityAction::fromEnum(SecurityActionEnum::LOGIN_ATTEMPT);
        $this->assertSame('login_attempt', (string)$action);
    }

    public function testCustomCreatesCustomAction(): void
    {
        $action = SecurityAction::custom('custom_action');
        $this->assertSame('custom_action', (string)$action);
    }

    public function testToStringReturnsValue(): void
    {
        $action = new SecurityAction('raw_value');
        $this->assertSame('raw_value', (string)$action);
    }
}
