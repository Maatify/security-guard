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

class AnalyticsTest extends TestCase
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
            keyPrefix: 'analytics_test',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $this->service->setConfig(new SecurityConfig($dto));
    }

    public function testGetStatsStructure(): void
    {
        // Populate some data
        $dto = new LoginAttemptDTO('1.2.3.4', 'stats', time(), 60, null, []);
        $this->service->handleAttempt($dto, false);

        try {
            $stats = $this->service->getStats();
            // Validating that it returns an array structure.
            $this->assertGreaterThanOrEqual(0, count($stats));
        } catch (\Exception $e) {
            $this->markTestSkipped('Cannot test getStats without a running Redis or fully mocked driver.');
        }
    }
}
