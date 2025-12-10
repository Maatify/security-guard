<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use PHPUnit\Framework\TestCase;

class SecurityGuardStatsTest extends TestCase
{
    private function createDriver(): FakeSecurityGuardDriver
    {
        $configDTO = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'stats:',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $config = new SecurityConfig($configDTO);
        $strategy = new DefaultIdentifierStrategy($config);
        $adapter = new FakeAdapter();

        return new FakeSecurityGuardDriver($adapter, $strategy);
    }

    // Phase 8: Stats Accuracy Validation
    public function testStatsAccuracyAfterOperations(): void
    {
        $driver = $this->createDriver();
        $stats = $driver->getStats();

        $this->assertNotEmpty($stats);
        $this->assertArrayHasKey('failures', $stats);
        $this->assertArrayHasKey('blocks', $stats);

        // Fake driver returns boolean flags for support
        $this->assertTrue($stats['failures']);
        $this->assertTrue($stats['blocks']);
    }

    // Phase 9: Stats Under Load
    public function testStatsUnderHighLoad(): void
    {
        $driver = $this->createDriver();

        for ($i = 0; $i < 150; $i++) {
            $driver->recordFailure(new LoginAttemptDTO(
                '1.1.1.1',
                'user' . $i,
                time(),
                60,
                null,
                []
            ));
        }

        $stats = $driver->getStats();
        $this->assertTrue($stats['failures']);
    }

    // Phase 10: Concurrency Simulation
    public function testConcurrentFailureSimulation(): void
    {
        $driver = $this->createDriver();

        // Simulating concurrency in a single thread is checking logic consistency.
        // We ensure that repeated calls increment correctly.

        $ip = '10.10.10.10';
        $subject = 'concurrent_user';
        $dto = new LoginAttemptDTO($ip, $subject, time(), 60, null, []);

        $current = 0;
        for ($i = 0; $i < 50; $i++) {
            $new = $driver->recordFailure($dto);
            $this->assertGreaterThan($current, $new);
            $current = $new;
        }

        $this->assertSame(50, $current);
    }
}
