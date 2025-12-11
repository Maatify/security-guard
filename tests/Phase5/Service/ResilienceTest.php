<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Service;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class ResilienceTest extends TestCase
{
    private SecurityGuardService $service;

    protected function setUp(): void
    {
        $this->service = new SecurityGuardService(
            new FakeAdapter(),
            new FakeIdentifierStrategy()
        );
    }

    public function testSimulatedRaceConditionHandlesIncrementsCorrectly(): void
    {
        // Since we can't easily spawn threads in PHPUnit without extensions,
        // we simulate "interleaved" calls by calling handleAttempt in sequence
        // but verify consistency.

        // This is a "logical" check that if multiple attempts happen, state is preserved.

        $dto = new LoginAttemptDTO('1.2.3.4', 'race', time(), []);

        $c1 = $this->service->handleAttempt($dto, false);
        $c2 = $this->service->handleAttempt($dto, false);
        $c3 = $this->service->handleAttempt($dto, false);

        $this->assertSame(1, $c1);
        $this->assertSame(2, $c2);
        $this->assertSame(3, $c3);
    }
}
