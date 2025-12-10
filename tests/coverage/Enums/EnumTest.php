<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Enums;

use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use Maatify\SecurityGuard\Enums\SecurityEventTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function testBlockTypeEnum(): void
    {
        $this->assertSame('auto', BlockTypeEnum::AUTO->value);
        $this->assertSame('manual', BlockTypeEnum::MANUAL->value);
        $this->assertSame('system', BlockTypeEnum::SYSTEM->value);
    }

    public function testSecurityActionEnum(): void
    {
        $this->assertSame('login_attempt', SecurityActionEnum::LOGIN_ATTEMPT->value);
        $this->assertSame('login_success', SecurityActionEnum::LOGIN_SUCCESS->value);
        $this->assertSame('login_failure', SecurityActionEnum::LOGIN_FAILURE->value);
        $this->assertSame('block_created', SecurityActionEnum::BLOCK_CREATED->value);
        $this->assertSame('block_removed', SecurityActionEnum::BLOCK_REMOVED->value);
    }

    public function testSecurityPlatformEnum(): void
    {
        $this->assertSame('web', SecurityPlatformEnum::WEB->value);
        $this->assertTrue(SecurityPlatformEnum::isBuiltin('web'));
        $this->assertFalse(SecurityPlatformEnum::isBuiltin('unknown'));
    }

    public function testSecurityEventTypeEnum(): void
    {
        $this->assertSame('login_failure', SecurityEventTypeEnum::LOGIN_FAILURE->value);
        $this->assertTrue(SecurityEventTypeEnum::isBuiltin('login_failure'));
        $this->assertFalse(SecurityEventTypeEnum::isBuiltin('unknown'));
    }

    public function testIdentifierModeEnum(): void
    {
        $this->assertSame('identifier_only', IdentifierModeEnum::IDENTIFIER_ONLY->value);
        $this->assertSame('identifier_and_ip', IdentifierModeEnum::IDENTIFIER_AND_IP->value);
        $this->assertSame('ip_only', IdentifierModeEnum::IP_ONLY->value);
    }
}
