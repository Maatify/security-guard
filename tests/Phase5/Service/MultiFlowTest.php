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

class MultiFlowTest extends TestCase
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
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($dto));
    }

    public function testIndependentCounters(): void
    {
        $dtoA = new LoginAttemptDTO('1.1.1.1', 'userA', time(), 60, null, []);
        $dtoB = new LoginAttemptDTO('2.2.2.2', 'userB', time(), 60, null, []);

        // Fail A (1/3)
        $this->service->handleAttempt($dtoA, false);

        // Fail B (1/3)
        $this->service->handleAttempt($dtoB, false);

        // Fail A (2/3)
        $countA = $this->service->handleAttempt($dtoA, false);
        $this->assertSame(2, $countA);

        // B should still be at 1
        $countB = $this->service->handleAttempt($dtoB, false);
        $this->assertSame(2, $countB);

        // Fail A (3/3) -> Block A
        $this->service->handleAttempt($dtoA, false);
        $this->assertTrue($this->service->isBlocked('1.1.1.1', 'userA'));

        // B should NOT be blocked
        $this->assertFalse($this->service->isBlocked('2.2.2.2', 'userB'));
    }

    public function testSameIpDifferentSubjects(): void
    {
        $ip = '10.0.0.1';
        $dtoA = new LoginAttemptDTO($ip, 'user1', time(), 60, null, []);
        $dtoB = new LoginAttemptDTO($ip, 'user2', time(), 60, null, []);

        // Fail user1 3 times -> block
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);

        $this->assertTrue($this->service->isBlocked($ip, 'user1'));

        // user2 should be fine
        $this->assertFalse($this->service->isBlocked($ip, 'user2'));
    }

    public function testSameSubjectDifferentIps(): void
    {
        $sub = 'target';
        $dtoA = new LoginAttemptDTO('1.1.1.1', $sub, time(), 60, null, []);
        $dtoB = new LoginAttemptDTO('2.2.2.2', $sub, time(), 60, null, []);

        // Fail IP 1 3 times
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);

        $this->assertTrue($this->service->isBlocked('1.1.1.1', $sub));

        // IP 2 should be fine
        $this->assertFalse($this->service->isBlocked('2.2.2.2', $sub));
    }
}
