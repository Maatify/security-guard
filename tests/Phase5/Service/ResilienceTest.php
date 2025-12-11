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

class ResilienceTest extends TestCase
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
            keyPrefix: 'resilience_test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($dto));
    }

    public function testSimulatedRaceConditionHandlesIncrementsCorrectly(): void
    {
        $dto = new LoginAttemptDTO('1.2.3.4', 'race', time(), 60, null, []);

        $c1 = $this->service->handleAttempt($dto, false);
        $c2 = $this->service->handleAttempt($dto, false);
        $c3 = $this->service->handleAttempt($dto, false);

        $this->assertSame(1, $c1);
        $this->assertSame(2, $c2);
        $this->assertSame(3, $c3);
    }
}
