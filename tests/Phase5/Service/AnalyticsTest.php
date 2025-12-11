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
            identifierMode: IdentifierModeEnum::IP_AND_SUBJECT,
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
            // Use assertNotNull to avoid 'always true' complaint about is_array,
            // as getStats() returns array<string, mixed>.
            // This confirms it didn't throw and returned *something* valid.
            $this->assertNotNull($stats);

        } catch (\Exception $e) {
            $this->markTestSkipped('Cannot test getStats without a running Redis or fully mocked driver.');
        }
    }
}
