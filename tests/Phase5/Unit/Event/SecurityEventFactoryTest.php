<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Event;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Event\SecurityAction;
use PHPUnit\Framework\TestCase;

class SecurityEventFactoryTest extends TestCase
{
    public function testFromLoginAttempt(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), 60, null, []);
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB);

        $event = SecurityEventFactory::fromLoginAttempt($dto, $platform, 1, 'admin');

        $this->assertSame('login_attempt', (string)$event->action);
        $this->assertSame('127.0.0.1', $event->ip);
    }

    public function testBlockCreated(): void
    {
        $block = new SecurityBlockDTO('1.1.1.1', 'me', BlockTypeEnum::AUTO, time() + 10, time());
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::SYSTEM);

        $event = SecurityEventFactory::blockCreated($block, $platform);

        $this->assertSame('block_created', (string)$event->action);
    }

    public function testBlockRemoved(): void
    {
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::ADMIN);
        $event = SecurityEventFactory::blockRemoved('1.1.1.1', 'me', $platform);

        $this->assertSame('block_removed', (string)$event->action);
    }

    public function testCleanup(): void
    {
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::SYSTEM);
        $event = SecurityEventFactory::cleanup($platform);

        // Memory says: `SecurityActionEnum` does not contain `CLEANUP`.
        // Memory says: `SecurityEventFactory::cleanup()` generates an event where the IP address is explicitly set to `'system'`.
        $this->assertSame('cleanup', (string)$event->action);
        $this->assertSame('system', $event->ip);
    }

    public function testCustom(): void
    {
        $action = SecurityAction::custom('alert');
        $platform = SecurityPlatform::custom('cron');

        $event = SecurityEventFactory::custom($action, $platform, '1.2.3.4', 'sub', ['foo' => 'bar']);

        $this->assertSame('alert', (string)$event->action);
        $this->assertSame('cron', (string)$event->platform);
        $this->assertSame('1.2.3.4', $event->ip);
    }
}
