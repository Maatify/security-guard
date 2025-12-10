<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\DTO;

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;

class SecurityBlockDTOTest extends TestCase
{
    public function testConstructAndGetters(): void
    {
        $dto = new SecurityBlockDTO('127.0.0.1', 'user', BlockTypeEnum::AUTO, 1234567890, 1234567800);
        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('user', $dto->subject);
        $this->assertSame(BlockTypeEnum::AUTO, $dto->type);
        $this->assertSame(1234567890, $dto->expiresAt);
        $this->assertSame(1234567800, $dto->createdAt);
    }
}
