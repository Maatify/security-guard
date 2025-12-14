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
        // Skip test if environment is not configured, instead of failing hard.
        // This allows the suite to pass in environments without Redis (e.g. basic CI).
        if (!getenv('REDIS_HOST') || !getenv('REDIS_PORT')) {
            $this->markTestSkipped('REDIS_HOST or REDIS_PORT not set. Skipping Redis integration tests.');
        }
    }

    protected function createAdapter(): AdapterInterface
    {
        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT');

        if (!$host || !$port) {
            // Should be caught by validateEnvironment(), but for safety:
            $this->markTestSkipped('Redis configuration missing.');
        }

        $port = (int)$port;

        return new class($host, $port) implements AdapterInterface {
            private \Redis $redis;
            private bool $connected = false;

            public function __construct(private string $host, private int $port) {
                // Ensure Redis class exists (polyfill check)
                if (!class_exists('Redis')) {
                    throw new \RuntimeException('Redis extension not loaded.');
                }

                $this->redis = new \Redis();
                try {
                     // @phpstan-ignore-next-line
                     $this->connected = $this->redis->connect($this->host, $this->port);
                } catch (\Throwable) {
                    $this->connected = false;
                }
            }

            public function connect(): void {
                if (!$this->connected) {
                    try {
                        // @phpstan-ignore-next-line
                        $this->connected = $this->redis->connect($this->host, $this->port);
                    } catch (\Throwable) {
                        $this->connected = false;
                    }
                }
            }

            public function disconnect(): void {
                if ($this->connected) {
                    // @phpstan-ignore-next-line
                    $this->redis->close();
                    $this->connected = false;
                }
            }

            public function isConnected(): bool {
                try {
                    // @phpstan-ignore-next-line
                    return $this->connected && $this->redis->ping();
                } catch (\Throwable) {
                    return false;
                }
            }

            public function healthCheck(): bool {
                return $this->isConnected();
            }

            /**
             * @return object
             */
            public function getDriver(): object {
                return $this->redis;
            }

            /**
             * @return object
             */
            public function getConnection(): object {
                return $this->redis;
            }
        };
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->adapter->isConnected()) {
            $this->markTestSkipped('Redis adapter failed to connect to the configured host (Connection refused/timed out).');
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
        // Note: RedisSecurityGuard does not have checkAttempts().
        // We rely on the return value of recordFailure() which we already asserted.

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
