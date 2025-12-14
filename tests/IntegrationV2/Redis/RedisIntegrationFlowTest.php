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
use Maatify\DataAdapters\Adapters\RedisAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Tests\IntegrationV2\BaseIntegrationV2TestCase;

/**
 * RedisIntegrationFlowTest
 *
 * Verifies the full authenticated login failure and blocking flow using a real Redis adapter.
 *
 * Flow:
 * Authenticated subject -> Record Failures -> Max Failures Reached -> Block Applied -> Unblock -> Verify Unblock
 */
class RedisIntegrationFlowTest extends BaseIntegrationV2TestCase
{
    private ?RedisSecurityGuard $guard = null;

    protected function validateEnvironment(): void
    {
        // V2 Policy: No skipping.
        // We do not enforce env vars existence here to allow defaults in createAdapter.
        // Connection failure in setUp() will handle the "Fail explicitly" requirement.
    }

    protected function createAdapter(): AdapterInterface
    {
        // Allow defaults to support standard local development without explicit env vars,
        // matching the behavior of legacy RealRedisAdapter but using the strict Vendor Adapter.
        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = getenv('REDIS_PORT') ? (int)getenv('REDIS_PORT') : 6379;

        // Fix: Use positional arguments for EnvironmentConfig to avoid unknown named parameter errors.
        // Assuming signature is (host, port, ...)
        $config = new EnvironmentConfig($host, $port);

        return new RedisAdapter($config);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // STRICT: Fail if not connected.
        if (!$this->adapter->isConnected()) {
            $host = getenv('REDIS_HOST') ?: '127.0.0.1';
            $port = getenv('REDIS_PORT') ?: '6379';
            $this->fail(sprintf('Redis adapter failed to connect to %s:%s', $host, $port));
        }

        $this->guard = new RedisSecurityGuard($this->adapter, $this->identifierStrategy);
    }

    public function testAuthenticatedSubjectBlockFlow(): void
    {
        // Assert guard is initialized to satisfy PHPStan nullable check
        $this->assertNotNull($this->guard, 'Guard should have been initialized in setUp');
        $guard = $this->guard;

        // 1. Setup Identity
        $ip = '192.168.1.100';
        $subject = 'user_' . bin2hex(random_bytes(4)); // Authenticated subject (non-trivial)

        // Ensure we start clean for this subject (implicit prefix isolation handles this generally,
        // but explicit reset ensures local state is clear).
        $guard->resetAttempts($ip, $subject);
        $guard->unblock($ip, $subject);

        // 2. Record Login Failures
        // Config: maxFailures = 5
        $maxFailures = 5;
        $attempt = new LoginAttemptDTO(
            ip: $ip,
            subject: $subject,
            occurredAt: time(),
            resetAfter: 60
        );

        for ($i = 1; $i <= $maxFailures; $i++) {
            $count = $guard->recordFailure($attempt);
            $this->assertSame($i, $count, "Failure count should increment to $i");

            if ($i < $maxFailures) {
                $this->assertFalse($guard->isBlocked($ip, $subject), "Should not be blocked at attempt $i");
            }
        }

        // 3. Trigger Block (Simulating Service Decision based on Count)

        // Apply Block
        $blockDTO = new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 300,
            createdAt: time()
        );
        $guard->block($blockDTO);

        // Verify Blocked
        $this->assertTrue($guard->isBlocked($ip, $subject), 'Subject should be blocked after applying block');
        $activeBlock = $guard->getActiveBlock($ip, $subject);
        $this->assertNotNull($activeBlock);
        $this->assertSame($subject, $activeBlock->subject);

        // 4. Unblock
        $guard->unblock($ip, $subject);

        // 5. Verify Unblock Success
        $this->assertFalse($guard->isBlocked($ip, $subject), 'Subject should be unblocked');
        $this->assertNull($guard->getActiveBlock($ip, $subject));
    }
}
