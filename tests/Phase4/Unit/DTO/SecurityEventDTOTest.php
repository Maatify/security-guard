<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\DTO;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;

class SecurityEventDTOTest extends TestCase
{
    public function testConstructorInitializesAllFields(): void
    {
        $action = SecurityAction::fromEnum(SecurityActionEnum::LOGIN_ATTEMPT);
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB);
        $timestamp = time();
        $context = ['key' => 'value'];

        $dto = new SecurityEventDTO(
            eventId: 'event-123',
            action: $action,
            platform: $platform,
            timestamp: $timestamp,
            ip: '127.0.0.1',
            subject: 'test-subject',
            userId: 123,
            userType: 'admin',
            context: $context
        );

        $this->assertSame('event-123', $dto->eventId);
        $this->assertSame($action, $dto->action);
        $this->assertSame($platform, $dto->platform);
        $this->assertSame($timestamp, $dto->timestamp);
        $this->assertSame('127.0.0.1', $dto->ip);
        $this->assertSame('test-subject', $dto->subject);
        $this->assertSame(123, $dto->userId);
        $this->assertSame('admin', $dto->userType);
        $this->assertSame($context, $dto->context);
    }

    public function testJsonSerializeOutputsExpectedStructure(): void
    {
        $action = SecurityAction::fromEnum(SecurityActionEnum::LOGIN_ATTEMPT);
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB);
        $timestamp = 1700000000;
        $context = ['foo' => 'bar'];

        $dto = new SecurityEventDTO(
            eventId: 'event-123',
            action: $action,
            platform: $platform,
            timestamp: $timestamp,
            ip: '192.168.1.1',
            subject: 'user@example.com',
            userId: 456,
            userType: 'customer',
            context: $context
        );

        $json = $dto->jsonSerialize();

        $this->assertSame('event-123', $json['event_id']);
        $this->assertSame('login_attempt', $json['action']);
        $this->assertSame('web', $json['platform']);
        $this->assertSame($timestamp, $json['timestamp']);
        $this->assertSame('192.168.1.1', $json['ip']);
        $this->assertSame('user@example.com', $json['subject']);
        $this->assertSame(456, $json['user_id']);
        $this->assertSame('customer', $json['user_type']);
        $this->assertSame($context, $json['context']);
    }

    public function testLoginAttemptDTOToEvent(): void
    {
        $loginAttempt = new LoginAttemptDTO(
            ip: '127.0.0.1',
            subject: 'test',
            occurredAt: time(),
            resetAfter: 300,
            context: ['attempt' => 1]
        );

        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::API);
        $event = $loginAttempt->toEvent($platform, 123, 'user');

        $this->assertInstanceOf(SecurityEventDTO::class, $event);
        $this->assertSame('login_attempt', (string)$event->action);
        $this->assertSame('api', (string)$event->platform);
        $this->assertSame(123, $event->userId);
        $this->assertSame('user', $event->userType);
        $this->assertSame('127.0.0.1', $event->ip);
        $this->assertSame('test', $event->subject);
        $this->assertArrayHasKey('attempt', $event->context);
        $this->assertSame(1, $event->context['attempt']);
        $this->assertSame(300, $event->context['reset_after']);
    }

    public function testSecurityBlockDTOToCreatedEvent(): void
    {
        $block = new SecurityBlockDTO(
            ip: '10.0.0.1',
            subject: 'hacker',
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 3600,
            createdAt: time()
        );

        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::SYSTEM);
        $event = $block->toCreatedEvent($platform, null, null);

        $this->assertInstanceOf(SecurityEventDTO::class, $event);
        $this->assertSame('block_created', (string)$event->action);
        $this->assertSame('system', (string)$event->platform);
        $this->assertNull($event->userId);
        $this->assertNull($event->userType);
        $this->assertSame('10.0.0.1', $event->ip);
        $this->assertSame('hacker', $event->subject);

        $this->assertArrayHasKey('block_type', $event->context);
        $this->assertArrayHasKey('expires_at', $event->context);
        $this->assertSame('auto', $event->context['block_type']);
    }

    public function testSecurityBlockDTOToRemovedEvent(): void
    {
        $block = new SecurityBlockDTO(
            ip: '10.0.0.1',
            subject: 'hacker',
            type: BlockTypeEnum::MANUAL,
            expiresAt: time() + 3600,
            createdAt: time()
        );

        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::CLI);
        $event = $block->toRemovedEvent($platform, 999, 'admin');

        $this->assertInstanceOf(SecurityEventDTO::class, $event);
        $this->assertSame('block_removed', (string)$event->action);
        $this->assertSame('cli', (string)$event->platform);
        $this->assertSame(999, $event->userId);
        $this->assertSame('admin', $event->userType);
        $this->assertSame('10.0.0.1', $event->ip);
        $this->assertSame('hacker', $event->subject);

        $this->assertEmpty($event->context);
    }

    public function testSecurityEventDTOEmptyContext(): void
    {
        $dto = new SecurityEventDTO(
            eventId: 'evt-1',
            action: SecurityAction::custom('test'),
            platform: SecurityPlatform::custom('test'),
            timestamp: time(),
            ip: '127.0.0.1',
            subject: 'subj',
            userId: null,
            userType: null,
            context: []
        );

        $this->assertEmpty($dto->context);
        $this->assertEmpty($dto->jsonSerialize()['context']);
    }
}
