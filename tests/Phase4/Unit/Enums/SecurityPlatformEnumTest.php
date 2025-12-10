<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Enums;

use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use PHPUnit\Framework\TestCase;

class SecurityPlatformEnumTest extends TestCase
{
    public function testCases(): void
    {
        $this->assertSame('web', SecurityPlatformEnum::WEB->value);
        $this->assertSame('api', SecurityPlatformEnum::API->value);
        $this->assertSame('cli', SecurityPlatformEnum::CLI->value);
        $this->assertSame('system', SecurityPlatformEnum::SYSTEM->value);
    }
}
