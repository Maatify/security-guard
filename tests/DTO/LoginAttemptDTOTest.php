<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\DTO;

use DateTimeImmutable;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
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
class LoginAttemptDTOTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user');
        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('user', $dto->username);
        $this->assertInstanceOf(DateTimeImmutable::class, $dto->occurredAt);
        $this->assertNull($dto->userAgent);
    }

    public function testJsonSerialize(): void
    {
        $time = new DateTimeImmutable();
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', $time, 'Mozilla/5.0');

        $json = $dto->jsonSerialize();
        $this->assertSame('127.0.0.1', $json['ip']);
        $this->assertSame('user', $json['username']);
        $this->assertSame($time->format(DateTimeImmutable::ATOM), $json['occurred_at']);
        $this->assertSame('Mozilla/5.0', $json['user_agent']);
    }
}
