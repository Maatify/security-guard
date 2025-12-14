<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-24 10:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\IntegrationV2\Redis;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Tests\IntegrationV2\BaseIntegrationV2TestCase;

/**
 * RedisTTLExpiryTest
 *
 * Verifies real Redis TTL expiry behavior using IntegrationV2 architecture.
 */
class RedisTTLExpiryTest extends BaseIntegrationV2TestCase
{
    private ?RedisSecurityGuard $guard = null;

    protected function validateEnvironment(): void
    {
        // STRICT: Environment validation is delegated to DatabaseResolver / EnvironmentLoader.
    }

    protected function createAdapter(): AdapterInterface
    {
        // STRICT: Use DatabaseResolver to fetch the configured Redis adapter.
        $config = new EnvironmentConfig(__DIR__ . '/../../');
        $resolver = new DatabaseResolver($config);

        // Resolve 'redis.cache' profile with auto-connect enabled
        return $resolver->resolve('redis.cache', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // STRICT: Fail if not connected. No skipping allowed.
        if (!$this->adapter->isConnected()) {
            $this->fail('Redis adapter (redis.cache) failed to connect. Ensure DSN configuration is valid and Redis is running.');
        }

        $this->guard = new RedisSecurityGuard($this->adapter, $this->identifierStrategy);
    }

    public function testRedisTTLExpiry(): void
    {
        // Assert guard is initialized to satisfy PHPStan nullable check
        $this->assertNotNull($this->guard, 'Guard should have been initialized in setUp');
        $guard = $this->guard;

        // 1. Setup Identity
        $ip = '192.168.1.101';
        $subject = 'ttl_user_' . bin2hex(random_bytes(4));

        // Ensure clean state
        $guard->resetAttempts($ip, $subject);
        $guard->unblock($ip, $subject);

        // 2. Apply Block with Short TTL (5 seconds)
        $ttlSeconds = 5;
        $expiryTime = time() + $ttlSeconds;

        $block = new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: BlockTypeEnum::AUTO,
            expiresAt: $expiryTime,
            createdAt: time()
        );

        $guard->block($block);

        // 3. Immediately Assert Blocked
        $this->assertTrue($guard->isBlocked($ip, $subject), 'Subject should be immediately blocked.');

        // Verify TTL is roughly correct (allow some variance)
        $remaining = $guard->getRemainingBlockSeconds($ip, $subject);
        $this->assertNotNull($remaining);
        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual($ttlSeconds, $remaining);

        // 4. Wait for Expiry
        // We wait TTL + 1 second to ensure expiry
        sleep($ttlSeconds + 1);

        // 5. Assert Expired
        $this->assertFalse($guard->isBlocked($ip, $subject), 'Subject should be unblocked after TTL expiry.');
        $this->assertNull($guard->getActiveBlock($ip, $subject), 'Active block should be null after expiry.');
    }
}
