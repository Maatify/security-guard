<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 14:50:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */
declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\DTO;

use DateTimeImmutable;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PHPUnit\Framework\TestCase;

final class SecurityBlockDTOTest extends TestCase
{
    public function testTemporaryBlockRemainingSeconds(): void
    {
        $now = new DateTimeImmutable();
        $expiresAt = $now->modify('+60 seconds');

        $dto = new SecurityBlockDTO(
            ip: '127.0.0.1',
            reason: 'Too many attempts',
            blockedAt: $now,
            expiresAt: $expiresAt,
            blockType: BlockTypeEnum::AUTO
        );

        $remaining = $dto->getRemainingSeconds();

        $this->assertIsInt($remaining);
        $this->assertGreaterThan(0, $remaining);
        $this->assertFalse($dto->isExpired());
    }

    public function testExpiredTemporaryBlock(): void
    {
        $now = new DateTimeImmutable();
        $expiredAt = $now->modify('-10 seconds');

        $dto = new SecurityBlockDTO(
            ip: '127.0.0.1',
            reason: 'Expired block',
            blockedAt: $now->modify('-60 seconds'),
            expiresAt: $expiredAt,
            blockType: BlockTypeEnum::AUTO
        );

        $this->assertSame(0, $dto->getRemainingSeconds());
        $this->assertTrue($dto->isExpired());
    }

    public function testPermanentBlock(): void
    {
        $dto = new SecurityBlockDTO(
            ip: '192.168.0.1',
            reason: 'Manual admin ban',
            blockedAt: new DateTimeImmutable(),
            expiresAt: null,
            blockType: BlockTypeEnum::MANUAL
        );

        $this->assertNull($dto->getRemainingSeconds());
        $this->assertFalse($dto->isExpired());
    }

    public function testJsonSerialization(): void
    {
        $now = new DateTimeImmutable();
        $dto = new SecurityBlockDTO(
            ip: '10.0.0.1',
            reason: 'Test',
            blockedAt: $now,
            expiresAt: null,
            blockType: BlockTypeEnum::SYSTEM
        );

        $data = $dto->jsonSerialize();

        $this->assertSame('10.0.0.1', $data['ip']);
        $this->assertSame('Test', $data['reason']);
        $this->assertSame($now->format(DateTimeImmutable::ATOM), $data['blocked_at']);
        $this->assertNull($data['expires_at']);
        $this->assertSame('system', $data['block_type']);
        $this->assertNull($data['remaining_seconds']);
        $this->assertFalse($data['is_expired']);
    }
}
