<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration\Redis;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Drivers\Support\RealRedisAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @group redis
 */
class RedisIntegrationTest extends TestCase
{
    private ?RealRedisAdapter $adapter = null;
    private ?RedisSecurityGuard $guard = null;
    private string $prefix;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('INTEGRATION_TESTS') !== '1') {
            $this->markTestSkipped('Integration tests are disabled (INTEGRATION_TESTS != 1)');
        }

        if (! extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded');
        }

        $this->adapter = new RealRedisAdapter();

        if (! $this->adapter->isConnected()) {
            $this->markTestSkipped('Cannot connect to real Redis at 127.0.0.1:6379');
        }

        // Use a unique prefix to avoid collisions and allow easy cleanup
        $this->prefix = 'test_run_' . bin2hex(random_bytes(4));

        // Create dependencies
        $configDto = new SecurityConfigDTO();
        $config = new SecurityConfig($configDto);
        $strategy = new DefaultIdentifierStrategy($config);

        $this->guard = new RedisSecurityGuard($this->adapter, $strategy);
    }

    protected function tearDown(): void
    {
        // We rely on the unique IDs (prefixes) to isolate tests.
        // Redis data is not explicitly cleared here to avoid flushing the entire DB,
        // but specific keys are short-lived or irrelevant to other runs.

        $this->adapter = null;
        $this->guard = null;
        parent::tearDown();
    }

    // Helper to generate a unique IP for this test method
    private function getUniqueIp(): string
    {
        return '127.0.0.' . mt_rand(1, 254);
    }

    // Helper to generate a unique Subject
    private function getUniqueSubject(): string
    {
        return 'user_' . uniqid() . '_' . $this->prefix;
    }

    public function testRecordFailureIncreasesAttemptsCount(): void
    {
        $ip = $this->getUniqueIp();
        $subject = $this->getUniqueSubject();

        // 1. First failure
        $attempt1 = new LoginAttemptDTO($ip, $subject, time(), 60);
        $count1 = $this->guard->recordFailure($attempt1);
        $this->assertSame(1, $count1);

        // 2. Second failure
        $attempt2 = new LoginAttemptDTO($ip, $subject, time(), 60);
        $count2 = $this->guard->recordFailure($attempt2);
        $this->assertSame(2, $count2);

        // Cleanup
        $this->guard->resetAttempts($ip, $subject);
    }

    public function testBlockCreatesActiveBlock(): void
    {
        $ip = $this->getUniqueIp();
        $subject = $this->getUniqueSubject();

        $blockDto = new SecurityBlockDTO(
            $ip,
            $subject,
            BlockTypeEnum::Manual,
            time() + 3600, // expires in 1 hour
            time()
        );

        $this->guard->block($blockDto);

        $activeBlock = $this->guard->getActiveBlock($ip, $subject);

        $this->assertNotNull($activeBlock);
        $this->assertSame($ip, $activeBlock->ip);
        $this->assertSame($subject, $activeBlock->subject);
        $this->assertSame(BlockTypeEnum::Manual, $activeBlock->type);

        // Cleanup
        $this->guard->unblock($ip, $subject);
    }

    public function testTimedBlockExpiresCorrectly(): void
    {
        $ip = $this->getUniqueIp();
        $subject = $this->getUniqueSubject();

        // Create a block that expires in 1 second
        $blockDto = new SecurityBlockDTO(
            $ip,
            $subject,
            BlockTypeEnum::Soft,
            time() + 1,
            time()
        );

        $this->guard->block($blockDto);

        // Verify it exists
        $this->assertNotNull($this->guard->getActiveBlock($ip, $subject));

        // Wait 2 seconds
        sleep(2);

        // Verify it is gone
        $this->assertNull($this->guard->getActiveBlock($ip, $subject));

        // Ensure no remaining block seconds
        $this->assertNull($this->guard->getRemainingBlockSeconds($ip, $subject));
    }

    public function testPermanentBlockReturnsNullTTL(): void
    {
        $ip = $this->getUniqueIp();
        $subject = $this->getUniqueSubject();

        // Permanent block (expiresAt = 0)
        $blockDto = new SecurityBlockDTO(
            $ip,
            $subject,
            BlockTypeEnum::Hard,
            0,
            time()
        );

        $this->guard->block($blockDto);

        $activeBlock = $this->guard->getActiveBlock($ip, $subject);
        $this->assertNotNull($activeBlock);
        $this->assertSame(0, $activeBlock->expiresAt);

        // getRemainingBlockSeconds should return null for permanent blocks
        $this->assertNull($this->guard->getRemainingBlockSeconds($ip, $subject));

        // Cleanup
        $this->guard->unblock($ip, $subject);
    }

    public function testUnblockRemovesBlock(): void
    {
        $ip = $this->getUniqueIp();
        $subject = $this->getUniqueSubject();

        $blockDto = new SecurityBlockDTO(
            $ip,
            $subject,
            BlockTypeEnum::Manual,
            time() + 3600,
            time()
        );

        $this->guard->block($blockDto);
        $this->assertNotNull($this->guard->getActiveBlock($ip, $subject));

        $this->guard->unblock($ip, $subject);
        $this->assertNull($this->guard->getActiveBlock($ip, $subject));
    }

    public function testCleanupDoesNotBreakExistingData(): void
    {
        // Setup some data
        $ip = $this->getUniqueIp();
        $subject = $this->getUniqueSubject();

        $attempt = new LoginAttemptDTO($ip, $subject, time(), 60);
        $this->guard->recordFailure($attempt);

        // Run cleanup
        $this->guard->cleanup();

        // Ensure data still exists
        $count = $this->guard->recordFailure($attempt);
        $this->assertSame(2, $count);

        // Cleanup
        $this->guard->resetAttempts($ip, $subject);
    }

    public function testStatsReturnExpectedKeys(): void
    {
        $stats = $this->guard->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('driver', $stats);
        $this->assertSame('redis', $stats['driver']);
        $this->assertArrayHasKey('connected', $stats);
        $this->assertTrue($stats['connected']);
        $this->assertArrayHasKey('redis_info', $stats);
        $this->assertNotEmpty($stats['redis_info']);
    }
}
