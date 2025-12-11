<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\DTO;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use PHPUnit\Framework\TestCase;

class LoginAttemptDTOTest extends TestCase
{
    public function testConstructAndJsonSerialize(): void
    {
        $dto = new LoginAttemptDTO(
            ip: '192.168.1.1',
            subject: 'user123',
            occurredAt: 1234567890,
            context: ['browser' => 'chrome']
        );

        $json = $dto->jsonSerialize();

        $this->assertSame('192.168.1.1', $json['ip']);
        $this->assertSame('user123', $json['subject']);
        $this->assertSame(1234567890, $json['occurredAt']);
        $this->assertSame(['browser' => 'chrome'], $json['context']);
    }

    public function testNowFactory(): void
    {
        $dto = LoginAttemptDTO::now(
            ip: '10.0.0.1',
            subject: 'admin',
            context: ['foo' => 'bar']
        );

        $this->assertGreaterThanOrEqual(time() - 1, $dto->occurredAt);
        $this->assertLessThanOrEqual(time() + 1, $dto->occurredAt);
        $this->assertSame('10.0.0.1', $dto->ip);
    }

    public function testToEvent(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'test', time(), []);
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB);

        $event = $dto->toEvent($platform, 100, 'admin');

        $this->assertSame('127.0.0.1', $event->ip);
        $this->assertSame('test', $event->subject);
        $this->assertSame(100, $event->userId);
        $this->assertSame('admin', $event->userType);
    }
}
