<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Enums;

use Maatify\SecurityGuard\Enums\SecurityEventTypeEnum;
use PHPUnit\Framework\TestCase;

class SecurityEventTypeEnumTest extends TestCase
{
    public function testCases(): void
    {
        $this->assertSame('login_failure', SecurityEventTypeEnum::LOGIN_FAILURE->value);
        $this->assertSame('login_success', SecurityEventTypeEnum::LOGIN_SUCCESS->value);
        $this->assertSame('block_created', SecurityEventTypeEnum::BLOCK_CREATED->value);
        $this->assertSame('block_removed', SecurityEventTypeEnum::BLOCK_REMOVED->value);
        $this->assertSame('cleanup', SecurityEventTypeEnum::CLEANUP->value);
        $this->assertSame('suspicious_activity', SecurityEventTypeEnum::SUSPICIOUS_ACTIVITY->value);
        $this->assertSame('rate_limit_violation', SecurityEventTypeEnum::RATE_LIMIT_VIOLATION->value);
    }

    public function testIsBuiltin(): void
    {
        $this->assertTrue(SecurityEventTypeEnum::isBuiltin('login_failure'));
        $this->assertFalse(SecurityEventTypeEnum::isBuiltin('custom_event'));
    }
}
