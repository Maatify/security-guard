<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\DTO;

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use PHPUnit\Framework\TestCase;

class SecurityBlockDTOTest extends TestCase
{
    public function testConstructAndExpiry(): void
    {
        $now = time();
        $expires = $now + 3600;

        $dto = new SecurityBlockDTO(
            ip: '127.0.0.1',
            subject: 'user',
            type: BlockTypeEnum::AUTO,
            expiresAt: $expires,
            createdAt: $now
        );

        $this->assertFalse($dto->isExpired());
        $this->assertGreaterThan(0, $dto->getRemainingSeconds());
        $this->assertSame(BlockTypeEnum::AUTO, $dto->type);
    }

    public function testExpiredBlock(): void
    {
        $dto = new SecurityBlockDTO(
            ip: '127.0.0.1',
            subject: 'user',
            type: BlockTypeEnum::MANUAL,
            expiresAt: time() - 10,
            createdAt: time() - 20
        );

        $this->assertTrue($dto->isExpired());
        $this->assertSame(0, $dto->getRemainingSeconds());
    }

    public function testToCreatedEvent(): void
    {
        $dto = new SecurityBlockDTO('127.0.0.1', 'user', BlockTypeEnum::MANUAL, time() + 60, time());
        $event = $dto->toCreatedEvent(SecurityPlatform::fromEnum(SecurityPlatformEnum::ADMIN), 1, 'admin');

        $this->assertSame('block_created', (string)$event->action);
        $this->assertSame('127.0.0.1', $event->ip);
    }

    public function testToRemovedEvent(): void
    {
        $dto = new SecurityBlockDTO('127.0.0.1', 'user', BlockTypeEnum::MANUAL, time() + 60, time());
        $event = $dto->toRemovedEvent(SecurityPlatform::fromEnum(SecurityPlatformEnum::ADMIN), 1, 'admin');

        $this->assertSame('block_removed', (string)$event->action);
    }
}
