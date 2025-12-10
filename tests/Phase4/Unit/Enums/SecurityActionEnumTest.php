<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Enums;

use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use PHPUnit\Framework\TestCase;

class SecurityActionEnumTest extends TestCase
{
    public function testCases(): void
    {
        $this->assertSame('login_attempt', SecurityActionEnum::LOGIN_ATTEMPT->value);
        $this->assertSame('block_created', SecurityActionEnum::BLOCK_CREATED->value);
        $this->assertSame('block_removed', SecurityActionEnum::BLOCK_REMOVED->value);
        $this->assertSame('cleanup', SecurityActionEnum::CLEANUP->value);
    }
}
