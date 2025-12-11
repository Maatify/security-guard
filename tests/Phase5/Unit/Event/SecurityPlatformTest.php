<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Event;

use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use PHPUnit\Framework\TestCase;

class SecurityPlatformTest extends TestCase
{
    public function testFromEnum(): void
    {
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::IOS);
        $this->assertSame('ios', (string)$platform);
    }

    public function testCustom(): void
    {
        $platform = SecurityPlatform::custom('console');
        $this->assertSame('console', (string)$platform);
    }
}
