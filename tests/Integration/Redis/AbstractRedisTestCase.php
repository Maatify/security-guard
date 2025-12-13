<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration\Redis;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Integration\BaseIntegrationTestCase;

abstract class AbstractRedisTestCase extends BaseIntegrationTestCase
{
    protected RedisSecurityGuard $driver;
    protected AdapterInterface $adapter;

    // Store identity for cleanup
    protected string $currentIp;
    protected string $currentSubject;

    abstract protected function createAdapter(): AdapterInterface;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requireEnv('REDIS_HOST');

        $this->adapter = $this->createAdapter();

        // AdapterInterface has connect()
        $this->adapter->connect();

        if (! $this->adapter->isConnected()) {
            $this->markTestSkipped('Could not connect to Redis server.');
        }

        $this->driver = new RedisSecurityGuard(
            $this->adapter,
            new FakeIdentifierStrategy()
        );

        // Initialize identity for this test
        $id = bin2hex(random_bytes(4));
        $this->currentIp = '127.0.0.1';
        $this->currentSubject = 'user_' . $id;
    }

    protected function tearDown(): void
    {
        if (isset($this->driver) && isset($this->currentIp) && isset($this->currentSubject)) {
            // Clean up resources created during the test
            $this->driver->resetAttempts($this->currentIp, $this->currentSubject);
            $this->driver->unblock($this->currentIp, $this->currentSubject);
        }

        parent::tearDown();
    }

    public function testRecordFailureIncreasesAttemptsCount(): void
    {
        $attempt = new LoginAttemptDTO(
            ip: $this->currentIp,
            subject: $this->currentSubject,
            occurredAt: time(),
            resetAfter: 300
        );

        $count1 = $this->driver->recordFailure($attempt);
        $this->assertSame(1, $count1);

        $count2 = $this->driver->recordFailure($attempt);
        $this->assertSame(2, $count2);
    }

    public function testBlockCreatesAnActiveBlock(): void
    {
        $block = new SecurityBlockDTO(
            ip: $this->currentIp,
            subject: $this->currentSubject,
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 300,
            createdAt: time()
        );

        $this->driver->block($block);

        $retrieved = $this->driver->getActiveBlock($this->currentIp, $this->currentSubject);

        $this->assertNotNull($retrieved);
        $this->assertSame($this->currentIp, $retrieved->ip);
        $this->assertSame($this->currentSubject, $retrieved->subject);
    }

    public function testTimedBlockExpiresCorrectly(): void
    {
        // To avoid "flaky timing assumptions" (sleep hacks), we verify the TTL is set correctly on the key.
        // This confirms Redis is managing the expiration.

        $expiresAt = time() + 100;

        $block = new SecurityBlockDTO(
            ip: $this->currentIp,
            subject: $this->currentSubject,
            type: BlockTypeEnum::AUTO,
            expiresAt: $expiresAt,
            createdAt: time()
        );

        $this->driver->block($block);

        // Verify it exists
        $this->assertNotNull($this->driver->getActiveBlock($this->currentIp, $this->currentSubject));

        // Verify TTL is approximately correct (99-100s)
        $remaining = $this->driver->getRemainingBlockSeconds($this->currentIp, $this->currentSubject);

        $this->assertNotNull($remaining);
        $this->assertGreaterThan(90, $remaining);
        $this->assertLessThanOrEqual(100, $remaining);
    }

    public function testPermanentBlockReturnsNullTTL(): void
    {
        $block = new SecurityBlockDTO(
            ip: $this->currentIp,
            subject: $this->currentSubject,
            type: BlockTypeEnum::MANUAL,
            expiresAt: 0, // Permanent
            createdAt: time()
        );

        $this->driver->block($block);

        $ttl = $this->driver->getRemainingBlockSeconds($this->currentIp, $this->currentSubject);
        $this->assertNull($ttl);

        $retrieved = $this->driver->getActiveBlock($this->currentIp, $this->currentSubject);
        $this->assertNotNull($retrieved);
        $this->assertSame(0, $retrieved->expiresAt);
    }

    public function testUnblockRemovesBlock(): void
    {
        $block = new SecurityBlockDTO(
            ip: $this->currentIp,
            subject: $this->currentSubject,
            type: BlockTypeEnum::MANUAL,
            expiresAt: 0,
            createdAt: time()
        );

        $this->driver->block($block);
        $this->assertNotNull($this->driver->getActiveBlock($this->currentIp, $this->currentSubject));

        $this->driver->unblock($this->currentIp, $this->currentSubject);
        $this->assertNull($this->driver->getActiveBlock($this->currentIp, $this->currentSubject));
    }

    public function testCleanupDoesNotBreakExistingData(): void
    {
        $block = new SecurityBlockDTO(
            ip: $this->currentIp,
            subject: $this->currentSubject,
            type: BlockTypeEnum::MANUAL,
            expiresAt: 0,
            createdAt: time()
        );

        $this->driver->block($block);

        // Run cleanup
        $this->driver->cleanup();

        // Verify data still exists
        $this->assertNotNull($this->driver->getActiveBlock($this->currentIp, $this->currentSubject));
    }

    public function testStatsReturnExpectedKeysAndValues(): void
    {
        $stats = $this->driver->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('driver', $stats);
        $this->assertSame('redis', $stats['driver']);
        $this->assertArrayHasKey('connected', $stats);
        $this->assertTrue($stats['connected']);
        $this->assertArrayHasKey('redis_info', $stats);
        $this->assertNotEmpty($stats['redis_info']);
    }
}
