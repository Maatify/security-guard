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

use InvalidArgumentException;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use PHPUnit\Framework\TestCase;

class LoginAttemptDTOTest extends TestCase
{
    public function testConstructorSuccess(): void
    {
        $dto = new LoginAttemptDTO(
            ip: '127.0.0.1',
            subject: 'login',
            occurredAt: 1234567890,
            resetAfter: 60,
            userAgent: 'Mozilla',
            context: ['foo' => 'bar']
        );

        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('login', $dto->subject);
        $this->assertSame(1234567890, $dto->occurredAt);
        $this->assertSame(60, $dto->resetAfter);
        $this->assertSame('Mozilla', $dto->userAgent);
        $this->assertSame(['foo' => 'bar'], $dto->context);
    }

    public function testConstructorThrowsOnEmptyIp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LoginAttemptDTO('', 'login', 1234567890, 60);
    }

    public function testConstructorThrowsOnEmptySubject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LoginAttemptDTO('127.0.0.1', '   ', 1234567890, 60);
    }

    public function testConstructorThrowsOnNegativeResetAfter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LoginAttemptDTO('127.0.0.1', 'login', 1234567890, -1);
    }

    public function testNowFactory(): void
    {
        $before = time();
        $dto = LoginAttemptDTO::now(
            ip: '10.0.0.1',
            subject: 'api',
            resetAfter: 120,
            userAgent: 'TestAgent',
            context: ['a' => 1]
        );
        $after = time();

        $this->assertSame('10.0.0.1', $dto->ip);
        $this->assertSame('api', $dto->subject);
        $this->assertSame(120, $dto->resetAfter);
        $this->assertSame('TestAgent', $dto->userAgent);
        $this->assertSame(['a' => 1], $dto->context);

        // occurredAt must be between before and after
        $this->assertGreaterThanOrEqual($before, $dto->occurredAt);
        $this->assertLessThanOrEqual($after, $dto->occurredAt);
    }

    public function testJsonSerialize(): void
    {
        $dto = new LoginAttemptDTO(
            ip: '1.1.1.1',
            subject: 'login',
            occurredAt: 222222,
            resetAfter: 30,
            userAgent: 'UA',
            context: ['k' => 'v']
        );

        $json = $dto->jsonSerialize();

        $this->assertSame('1.1.1.1', $json['ip']);
        $this->assertSame('login', $json['subject']);
        $this->assertSame(222222, $json['occurred_at']);
        $this->assertSame(30, $json['reset_after']);
        $this->assertSame('UA', $json['user_agent']);
        $this->assertSame(['k' => 'v'], $json['context']);
    }
}
