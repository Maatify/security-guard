<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 01:59
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers;

use Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface;
use Maatify\SecurityGuard\Drivers\Support\RedisClientProxy;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use RuntimeException;
use Throwable;

/**
 * 🚀 RedisSecurityGuard
 *
 * High-performance Redis driver that handles:
 * - Failure counting
 * - Auto-expiring attempts
 * - Block storage (hash)
 * - TTL-based blocks
 * - Instant retrieval
 *
 * @package Maatify\SecurityGuard\Drivers
 */
class RedisSecurityGuard extends AbstractSecurityGuardDriver implements SecurityGuardDriverInterface
{
    private RedisClientProxy $redis;

    public function __construct(
        \Maatify\Common\Contracts\Adapter\AdapterInterface $adapter,
        \Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface $strategy
    ) {
        parent::__construct($adapter, $strategy);

        $this->assertConnected();

        $raw = $adapter->getDriver(); // استخدم getDriver وليس getConnection

        // ✨ Type Safety (يحل مشكلة PHPStan بالكامل)
        if (!($raw instanceof \Redis) && !($raw instanceof \Predis\Client)) {
            throw new \InvalidArgumentException(
                'RedisSecurityGuard requires Redis or Predis client. Got: ' . get_debug_type($raw)
            );
        }

        /** @var \Redis|\Predis\Client $raw */
        $this->redis = new RedisClientProxy($raw);
    }

    // -------------------------------------------------------------------------
    //  IntegrationV2 safeguards
    // -------------------------------------------------------------------------

    private function assertConnected(): void
    {
        if (! $this->adapter->isConnected()) {
            throw new RuntimeException('IntegrationV2 Redis connection failed.');
        }
    }

    /**
     * @template TReturn
     * @param callable():TReturn $operation
     * @return TReturn
     */
    private function executeRedis(callable $operation, string $context)
    {
        try {
            return $operation();
        } catch (Throwable $e) {
            throw new RuntimeException('IntegrationV2 Redis connection failed.', 0, $e);
        }
    }

    // -------------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------------

    private function keyFailures(string $id): string
    {
        return "sg:fail:{$id}";
    }

    private function keyBlock(string $id): string
    {
        return "sg:block:{$id}";
    }

    // -------------------------------------------------------------------------
    //  Failure Logic
    // -------------------------------------------------------------------------

    protected function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        return $this->executeRedis(
            function () use ($attempt): int {
                $id = $this->makeIdentifier($attempt->ip, $attempt->subject, $attempt->context);

                $key = $this->keyFailures($id);

                // Redis INCR is atomic
                $count = $this->redis->incr($key);

                // Auto-expire attempts window
                if ($attempt->resetAfter > 0) {
                    $this->redis->expire($key, $attempt->resetAfter);
                }

                return (int)$count;
            },
            'recordFailure'
        );
    }

    protected function doResetAttempts(string $ip, string $subject): void
    {
        $this->executeRedis(
            function () use ($ip, $subject): void {
                $id = $this->makeIdentifier($ip, $subject);
                $this->redis->del($this->keyFailures($id));
            },
            'resetAttempts'
        );
    }

    // -------------------------------------------------------------------------
    //  Blocks
    // -------------------------------------------------------------------------

    protected function doBlock(SecurityBlockDTO $block): void
    {
        $this->executeRedis(
            function () use ($block): void {
                $id = $this->makeIdentifier($block->ip, $block->subject);
                $key = $this->keyBlock($id);

                $payload = $this->encodeBlock($block);

                // Save as Redis Hash
                $this->redis->hMSet($key, $payload);

                // TTL only if expiresAt != 0 (permanent)
                if ($block->expiresAt > 0) {
                    $ttl = max(1, $block->expiresAt - $this->now());
                    $this->redis->expire($key, $ttl);
                }
            },
            'block'
        );
    }

    protected function doUnblock(string $ip, string $subject): void
    {
        $this->executeRedis(
            function () use ($ip, $subject): void {
                $id = $this->makeIdentifier($ip, $subject);
                $this->redis->del($this->keyBlock($id));
            },
            'unblock'
        );
    }

    protected function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        return $this->executeRedis(
            function () use ($ip, $subject): ?SecurityBlockDTO {
                $id = $this->makeIdentifier($ip, $subject);

                $data = $this->redis->hGetAll($this->keyBlock($id));

                if (!is_array($data) || $data === []) {
                    return null;
                }

                // convert Redis string values to an expected mixed array
                $normalized = [];
                foreach ($data as $k => $v) {
                    $normalized[(string)$k] = $v;
                }

                if (isset($normalized['expires_at'])) {
                    $normalized['expires_at'] = (int) $normalized['expires_at'];
                }

                if (isset($normalized['created_at'])) {
                    $normalized['created_at'] = (int) $normalized['created_at'];
                }

                // Check if block is expired
                if (isset($normalized['expires_at'])) {
                    $expiresAt = (int) $normalized['expires_at'];
                    if ($expiresAt > 0 && $expiresAt <= $this->now()) {
                        // Block has expired - delete it and return null
                        $this->redis->del($this->keyBlock($id));
                        return null;
                    }
                }

                /** @var array<string,mixed> $normalized */
                return $this->decodeBlock($normalized);
            },
            'getActiveBlock'
        );
    }

    protected function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        return $this->executeRedis(
            function () use ($ip, $subject): ?int {
                $id = $this->makeIdentifier($ip, $subject);
                $key = $this->keyBlock($id);

                $ttl = $this->redis->ttl($key);

                if ($ttl < 0) {
                    // -1 (no expire) → permanent block OR no key
                    $block = $this->doGetActiveBlock($ip, $subject);

                    if ($block === null) {
                        return null;
                    }

                    return $block->expiresAt === 0 ? null : max(0, $block->expiresAt - $this->now());
                }

                return $ttl;
            },
            'getRemainingBlockSeconds'
        );
    }

    // -------------------------------------------------------------------------
    // Cleanup
    // -------------------------------------------------------------------------

    protected function doCleanup(): void
    {
        // Redis auto cleans expired keys, so nothing needed here.
        // But you COULD implement pattern cleanup if required.
    }

    // -------------------------------------------------------------------------
    // Stats
    // -------------------------------------------------------------------------

    public function doGetStats(): array
    {
        return $this->executeRedis(
            function (): array {
                return [
                    'driver'     => 'redis',
                    'connected'  => $this->adapter->isConnected(),
                    'redis_info' => $this->redis->info(),
                ];
            },
            'getStats'
        );
    }
}
