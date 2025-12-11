<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\DTO;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\SecurityAction;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use PHPUnit\Framework\TestCase;

class SecurityEventDTOTest extends TestCase
{
    public function testSerialization(): void
    {
        $dto = new SecurityEventDTO(
            eventId: 'evt_123',
            action: SecurityAction::fromEnum(SecurityActionEnum::LOGIN_FAILURE),
            platform: SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB),
            timestamp: 1234567890,
            ip: '192.168.0.1',
            subject: 'alice',
            userId: 55,
            userType: 'customer',
            context: ['browser' => 'firefox']
        );

        $json = $dto->jsonSerialize();

        $this->assertSame('evt_123', $json['event_id']);
        $this->assertSame('login_failure', $json['action']);
        $this->assertSame('web', $json['platform']);
        $this->assertSame(1234567890, $json['timestamp']);
        $this->assertSame(55, $json['user_id']);
    }
}
