<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 14:50:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */
declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\DTO;

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PHPUnit\Framework\TestCase;

class SecurityBlockDTOTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $dto = new SecurityBlockDTO(
            ip: '127.0.0.1',
            subject: 'user@example.com',
            type: BlockTypeEnum::AUTO,
            expiresAt: 1700000000,
            createdAt: 1600000000
        );

        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('user@example.com', $dto->subject);
        $this->assertSame(BlockTypeEnum::AUTO, $dto->type);
        $this->assertSame(1700000000, $dto->expiresAt);
        $this->assertSame(1600000000, $dto->createdAt);
    }

    public function testPermanentBlockRemainingSecondsIsNull(): void
    {
        $dto = new SecurityBlockDTO(
            '1.1.1.1',
            'test',
            BlockTypeEnum::MANUAL,
            expiresAt: 0,
            createdAt: time()
        );

        $this->assertNull($dto->getRemainingSeconds());
        $this->assertFalse($dto->isExpired());
    }

    public function testNonExpiredBlock(): void
    {
        $future = time() + 120;

        $dto = new SecurityBlockDTO(
            '2.2.2.2',
            'abc',
            BlockTypeEnum::AUTO,
            expiresAt: $future,
            createdAt: time()
        );

        $remaining = $dto->getRemainingSeconds();

        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(120, $remaining);
        $this->assertFalse($dto->isExpired());
    }

    public function testExpiredBlock(): void
    {
        $past = time() - 10;

        $dto = new SecurityBlockDTO(
            '3.3.3.3',
            'xyz',
            BlockTypeEnum::AUTO,
            expiresAt: $past,
            createdAt: time()
        );

        $this->assertSame(0, $dto->getRemainingSeconds());
        $this->assertTrue($dto->isExpired());
    }

    public function testJsonSerialize(): void
    {
        $now = time();
        $future = $now + 50;

        $dto = new SecurityBlockDTO(
            '10.0.0.1',
            'subject',
            BlockTypeEnum::MANUAL,
            expiresAt: $future,
            createdAt: $now
        );

        $json = $dto->jsonSerialize();

        $this->assertSame('10.0.0.1', $json['ip']);
        $this->assertSame('subject', $json['subject']);
        $this->assertSame(BlockTypeEnum::MANUAL->value, $json['type']);
        $this->assertSame($future, $json['expires_at']);
        $this->assertSame($now, $json['created_at']);

        $this->assertGreaterThan(0, $json['remaining_seconds']);
        $this->assertFalse($json['is_expired']);
    }
}
