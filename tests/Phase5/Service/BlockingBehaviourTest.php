<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class BlockingBehaviourTest extends TestCase
{
    private SecurityGuardService $service;

    protected function setUp(): void
    {
        $this->service = new SecurityGuardService(
            new FakeAdapter(),
            new FakeIdentifierStrategy()
        );
    }

    public function testManualBlockingOverridesCounters(): void
    {
        $ip = '10.0.0.5';
        $sub = 'manual_test';

        // Manual block
        $this->service->block(new SecurityBlockDTO(
            ip: $ip,
            subject: $sub,
            type: BlockTypeEnum::MANUAL,
            expiresAt: time() + 300,
            createdAt: time()
        ));

        $this->assertTrue($this->service->isBlocked($ip, $sub));

        // Even if we record success, it should remain blocked?
        // Note: handleAttempt checks isBlocked first.
        // If blocked, it returns remaining seconds.
        // It does NOT process success/failure if blocked.
        // (See SecurityGuardService::handleAttempt logic in my thought trace or implementation:
        //  1) Already blocked? return remaining.
        //  2) Success? reset.
        // )
        // So manual block prevents login.

        $dto = new \Maatify\SecurityGuard\DTO\LoginAttemptDTO($ip, $sub, time(), []);

        // Attempt (should be blocked)
        $result = $this->service->handleAttempt($dto, true); // Even if "success" passed, logic blocks it first
        $this->assertNotNull($result); // Returns remaining seconds
        $this->assertGreaterThan(0, $result);
    }

    public function testUnblock(): void
    {
        $ip = '10.0.0.6';
        $sub = 'unblock_test';

        $this->service->block(new SecurityBlockDTO(
            ip: $ip,
            subject: $sub,
            type: BlockTypeEnum::MANUAL,
            expiresAt: time() + 300,
            createdAt: time()
        ));

        $this->assertTrue($this->service->isBlocked($ip, $sub));

        $this->service->unblock($ip, $sub);

        $this->assertFalse($this->service->isBlocked($ip, $sub));
    }

    public function testPolicySwitching(): void
    {
        $dto = new \Maatify\SecurityGuard\DTO\LoginAttemptDTO('1.1.1.1', 'policy', time(), []);

        // 1. Strict Policy (1 failure = block)
        $this->service->setConfig(new SecurityConfig(1, 100));

        $this->service->handleAttempt($dto, false);
        $this->assertTrue($this->service->isBlocked('1.1.1.1', 'policy'));

        $this->service->unblock('1.1.1.1', 'policy');
        $this->service->resetAttempts('1.1.1.1', 'policy');

        // 2. Loose Policy (5 failures = block)
        $this->service->setConfig(new SecurityConfig(5, 100));

        $this->service->handleAttempt($dto, false);
        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'policy')); // 1/5

        $this->service->handleAttempt($dto, false); // 2/5
        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'policy'));
    }
}
