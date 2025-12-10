<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Event;

use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;

class SecurityPlatformTest extends TestCase
{
    public function testFromEnumCreatesPlatform(): void
    {
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB);
        $this->assertSame('web', (string)$platform);
    }

    public function testCustomCreatesCustomPlatform(): void
    {
        $platform = SecurityPlatform::custom('mobile_app');
        $this->assertSame('mobile_app', (string)$platform);
    }

    public function testToStringReturnsValue(): void
    {
        $platform = new SecurityPlatform('console');
        $this->assertSame('console', (string)$platform);
    }
}
