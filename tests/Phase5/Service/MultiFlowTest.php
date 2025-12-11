<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\Config\SecurityConfig;
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
        $this->service->setConfig(new SecurityConfig(3, 60));
    }

    public function testIndependentCounters(): void
    {
        $dtoA = new LoginAttemptDTO('1.1.1.1', 'userA', time(), []);
        $dtoB = new LoginAttemptDTO('2.2.2.2', 'userB', time(), []);

        // Fail A (1/3)
        $this->service->handleAttempt($dtoA, false);

        // Fail B (1/3)
        $this->service->handleAttempt($dtoB, false);

        // Fail A (2/3)
        $countA = $this->service->handleAttempt($dtoA, false);
        $this->assertSame(2, $countA);

        // B should still be at 1 (but we can't query count directly easily without handleAttempt or inspecting internal storage)
        // But let's fail B again
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
        $dtoA = new LoginAttemptDTO($ip, 'user1', time(), []);
        $dtoB = new LoginAttemptDTO($ip, 'user2', time(), []);

        // Fail user1 3 times -> block
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);

        $this->assertTrue($this->service->isBlocked($ip, 'user1'));

        // user2 should be fine (IdentifierStrategy uses ip:subject by default)
        $this->assertFalse($this->service->isBlocked($ip, 'user2'));
    }

    public function testSameSubjectDifferentIps(): void
    {
        $sub = 'target';
        $dtoA = new LoginAttemptDTO('1.1.1.1', $sub, time(), []);
        $dtoB = new LoginAttemptDTO('2.2.2.2', $sub, time(), []);

        // Fail IP 1 3 times
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);
        $this->service->handleAttempt($dtoA, false);

        $this->assertTrue($this->service->isBlocked('1.1.1.1', $sub));

        // IP 2 should be fine
        $this->assertFalse($this->service->isBlocked('2.2.2.2', $sub));
    }
}
