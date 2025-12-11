<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
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
        $dto = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 60,
            maxFailures: 3,
            identifierMode: IdentifierModeEnum::IP_AND_SUBJECT,
            keyPrefix: 'block_test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($dto));
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

        $dto = new \Maatify\SecurityGuard\DTO\LoginAttemptDTO($ip, $sub, time(), 300, null, []);

        // Attempt (should be blocked)
        $result = $this->service->handleAttempt($dto, true);
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
        $dto = new \Maatify\SecurityGuard\DTO\LoginAttemptDTO('1.1.1.1', 'policy', time(), 100, null, []);

        // 1. Strict Policy (1 failure = block)
        $strict = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 100,
            maxFailures: 1,
            identifierMode: IdentifierModeEnum::IP_AND_SUBJECT,
            keyPrefix: 'strict',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($strict));

        $this->service->handleAttempt($dto, false);
        $this->assertTrue($this->service->isBlocked('1.1.1.1', 'policy'));

        $this->service->unblock('1.1.1.1', 'policy');
        $this->service->resetAttempts('1.1.1.1', 'policy');

        // 2. Loose Policy (5 failures = block)
        $loose = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 100,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IP_AND_SUBJECT,
            keyPrefix: 'loose',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($loose));

        $this->service->handleAttempt($dto, false);
        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'policy')); // 1/5

        $this->service->handleAttempt($dto, false); // 2/5
        $this->assertFalse($this->service->isBlocked('1.1.1.1', 'policy'));
    }
}
