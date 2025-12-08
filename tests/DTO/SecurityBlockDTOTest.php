<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\DTO;

use DateTimeImmutable;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use PHPUnit\Framework\TestCase;

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
class SecurityBlockDTOTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $blockedAt = new DateTimeImmutable();
        $expiresAt = $blockedAt->modify('+1 hour');

        $dto = new SecurityBlockDTO('127.0.0.1', 'Too many attempts', $blockedAt, $expiresAt);

        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('Too many attempts', $dto->reason);
        $this->assertSame($blockedAt, $dto->blockedAt);
        $this->assertSame($expiresAt, $dto->expiresAt);
        $this->assertSame('auto', $dto->blockType);
    }

    public function testJsonSerialize(): void
    {
        $blockedAt = new DateTimeImmutable();
        $expiresAt = $blockedAt->modify('+1 hour');

        $dto = new SecurityBlockDTO('127.0.0.1', 'Manual block', $blockedAt, $expiresAt, 'manual');

        $json = $dto->jsonSerialize();
        $this->assertSame('127.0.0.1', $json['ip']);
        $this->assertSame('Manual block', $json['reason']);
        $this->assertSame($blockedAt->format(DateTimeImmutable::ATOM), $json['blocked_at']);
        $this->assertSame($expiresAt->format(DateTimeImmutable::ATOM), $json['expires_at']);
        $this->assertSame('manual', $json['block_type']);
    }
}
