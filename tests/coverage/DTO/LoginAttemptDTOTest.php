<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\DTO;

use InvalidArgumentException;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Event\SecurityAction;
use PHPUnit\Framework\TestCase;

class LoginAttemptDTOTest extends TestCase
{
    public function testConstruct(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', 1234567890, 60, 'agent', ['foo' => 'bar']);
        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('user', $dto->subject);
        $this->assertSame(1234567890, $dto->occurredAt);
        $this->assertSame(60, $dto->resetAfter);
        $this->assertSame('agent', $dto->userAgent);
        $this->assertSame(['foo' => 'bar'], $dto->context);
    }

    public function testConstructThrowsOnEmptyIp(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IP cannot be empty.');
        new LoginAttemptDTO('', 'user', 1234567890, 60);
    }

    public function testConstructThrowsOnEmptySubject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('subject cannot be empty.');
        new LoginAttemptDTO('127.0.0.1', '', 1234567890, 60);
    }

    public function testConstructThrowsOnNegativeResetAfter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('resetAfter cannot be negative.');
        new LoginAttemptDTO('127.0.0.1', 'user', 1234567890, -1);
    }

    public function testNow(): void
    {
        $dto = LoginAttemptDTO::now('127.0.0.1', 'user', 60, 'agent', ['foo' => 'bar']);
        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('user', $dto->subject);
        $this->assertGreaterThanOrEqual(time() - 1, $dto->occurredAt);
        $this->assertSame(60, $dto->resetAfter);
        $this->assertSame('agent', $dto->userAgent);
        $this->assertSame(['foo' => 'bar'], $dto->context);
    }

    public function testJsonSerialize(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', 1234567890, 60, 'agent', ['foo' => 'bar']);
        $json = json_encode($dto);
        $this->assertIsString($json);
        $array = json_decode($json, true);
        $this->assertSame('127.0.0.1', $array['ip']);
        $this->assertSame('user', $array['subject']);
        $this->assertSame(1234567890, $array['occurred_at']);
        $this->assertSame(60, $array['reset_after']);
        $this->assertSame('agent', $array['user_agent']);
        $this->assertSame(['foo' => 'bar'], $array['context']);
    }

    public function testToEvent(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', 1234567890, 60);
        $platform = SecurityPlatform::default();
        $event = $dto->toEvent($platform, 123, 'admin');

        $this->assertSame($dto->ip, $event->ip);
        $this->assertSame($dto->subject, $event->subject);
        $this->assertEquals($platform, $event->platform);
        $this->assertSame(123, $event->userId);
        $this->assertSame('admin', $event->userType);
        $this->assertSame('login_failure', (string)$event->action);
    }
}
