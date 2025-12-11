<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class LoginAttemptTest extends TestCase
{
    private SecurityGuardService $service;
    private FakeAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new FakeAdapter();
        $this->service = new SecurityGuardService(
            $this->adapter,
            new FakeIdentifierStrategy()
        );

        // Config: 3 failures allowed, block for 60s
        $dto = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 60,
            maxFailures: 3,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($dto));
    }

    public function testFailThenSuccessResetsCounter(): void
    {
        $dto = new LoginAttemptDTO('1.1.1.1', 'userA', time(), 60, null, []);

        // 1. Fail (Count: 1)
        $count = $this->service->handleAttempt($dto, false);
        $this->assertSame(1, $count);

        // 2. Fail (Count: 2)
        $count = $this->service->handleAttempt($dto, false);
        $this->assertSame(2, $count);

        // 3. Success (Reset)
        $result = $this->service->handleAttempt($dto, true);
        $this->assertNull($result);

        // 4. Fail (Count: 1)
        $count = $this->service->handleAttempt($dto, false);
        $this->assertSame(1, $count);
    }

    public function testThresholdTriggersAutoBlock(): void
    {
        $dto = new LoginAttemptDTO('1.1.1.1', 'userB', time(), 60, null, []);

        // Config max failures is 3

        // 1
        $this->service->handleAttempt($dto, false);
        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'userB'));

        // 2
        $this->service->handleAttempt($dto, false);
        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'userB'));

        // 3 -> Block
        $this->service->handleAttempt($dto, false);
        $this->assertTrue($this->service->isBlocked('1.1.1.1', 'userB'));

        // Check remaining time (approx 60s)
        $remaining = $this->service->getRemainingBlockSeconds('1.1.1.1', 'userB');
        $this->assertGreaterThan(55, $remaining);
        $this->assertLessThanOrEqual(60, $remaining);

        // Subsequent attempts should return remaining seconds immediately
        $result = $this->service->handleAttempt($dto, false);
        $this->assertNotNull($result);
        $this->assertGreaterThan(55, $result);
    }
}
