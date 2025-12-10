<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Unit\Factory;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use PHPUnit\Framework\TestCase;

class SecurityEventFactoryTest extends TestCase
{
    private SecurityEventFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SecurityEventFactory();
    }

    public function testFromLoginAttemptGeneratesValidEvent(): void
    {
        $dto = new LoginAttemptDTO('127.0.0.1', 'user', time(), ['foo' => 'bar']);
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB);

        $event = $this->factory->fromLoginAttempt($dto, $platform, 10, 'customer');

        $this->assertInstanceOf(SecurityEventDTO::class, $event);
        $this->assertSame('login_attempt', (string)$event->action);
        $this->assertSame('web', (string)$event->platform);
        $this->assertSame('127.0.0.1', $event->ip);
        $this->assertSame('user', $event->subject);
        $this->assertSame(10, $event->userId);
        $this->assertSame('customer', $event->userType);
        $this->assertSame(['foo' => 'bar'], $event->context);
    }

    public function testBlockCreatedGeneratesValidEvent(): void
    {
        $block = new SecurityBlockDTO('127.0.0.1', 'user', BlockTypeEnum::AUTO, time() + 60, time());
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::SYSTEM);

        $event = $this->factory->blockCreated($block, $platform);

        $this->assertSame('block_created', (string)$event->action);
        $this->assertSame('system', (string)$event->platform);
        $this->assertSame('127.0.0.1', $event->ip);
        $this->assertSame('user', $event->subject);
        $this->assertSame('auto', $event->context['block_type']);
    }

    public function testBlockRemovedGeneratesValidEvent(): void
    {
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::CLI);
        $event = $this->factory->blockRemoved('1.2.3.4', 'target', $platform, 99, 'admin');

        $this->assertSame('block_removed', (string)$event->action);
        $this->assertSame('cli', (string)$event->platform);
        $this->assertSame('1.2.3.4', $event->ip);
        $this->assertSame('target', $event->subject);
        $this->assertSame(99, $event->userId);
        $this->assertSame('admin', $event->userType);
    }

    public function testCleanupGeneratesSystemEvent(): void
    {
        $platform = SecurityPlatform::fromEnum(SecurityPlatformEnum::SYSTEM);
        $event = $this->factory->cleanup($platform);

        $this->assertSame('cleanup', (string)$event->action);
        $this->assertSame('system', (string)$event->platform);
        $this->assertSame('0.0.0.0', $event->ip);
        $this->assertSame('system', $event->subject);
    }

    public function testCustomGeneratesCustomEvent(): void
    {
        $action = SecurityAction::custom('my_custom_action');
        $platform = SecurityPlatform::custom('my_platform');

        $event = $this->factory->custom(
            $action,
            $platform,
            '8.8.8.8',
            'google',
            ['reason' => 'test'],
            1,
            'superuser'
        );

        $this->assertSame('my_custom_action', (string)$event->action);
        $this->assertSame('my_platform', (string)$event->platform);
        $this->assertSame('8.8.8.8', $event->ip);
        $this->assertSame('google', $event->subject);
        $this->assertSame(['reason' => 'test'], $event->context);
        $this->assertSame(1, $event->userId);
        $this->assertSame('superuser', $event->userType);
    }
}
