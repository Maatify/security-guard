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

final class LoginAttemptDTOTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $dto = LoginAttemptDTO::now(
            ip: '127.0.0.1',
            username: 'admin',
            userAgent: 'PHPUnit',
            context: ['key' => 'value']
        );

        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('admin', $dto->username);
        $this->assertSame('PHPUnit', $dto->userAgent);
        $this->assertSame(['key' => 'value'], $dto->context);
    }

    public function testJsonSerialization(): void
    {
        $dto = LoginAttemptDTO::now(
            ip: '1.1.1.1',
            username: 'user'
        );

        $data = $dto->jsonSerialize();

        $this->assertSame('1.1.1.1', $data['ip']);
        $this->assertSame('user', $data['username']);
        $this->assertArrayHasKey('occurred_at', $data);
        $this->assertNull($data['user_agent']);
        $this->assertSame([], $data['context']);
    }

    public function testEmptyIpThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LoginAttemptDTO('', 'user');
    }

    public function testEmptyUsernameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LoginAttemptDTO('127.0.0.1', '');
    }
}
