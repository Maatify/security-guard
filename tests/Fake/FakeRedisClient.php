<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:22
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Fake;

final class FakeRedisClient
{
    /**
     * @var array<string, int|array<string, int|string>>
     */
    private array $data = [];

    /**
     * @var array<string, int>
     */
    private array $ttlMap = []; // key => expires_at

    public function incr(string $key): int
    {
        $current = $this->data[$key] ?? 0;

        if (! is_int($current)) {
            $current = 0;
        }

        $this->data[$key] = $current + 1;

        return $this->data[$key];
    }

    public function expire(string $key, int $ttl): void
    {
        $this->ttlMap[$key] = time() + $ttl;
    }

    public function del(string $key): void
    {
        unset($this->data[$key], $this->ttlMap[$key]);
    }

    /**
     * @param array<string, int|string> $values
     */
    public function hMSet(string $key, array $values): void
    {
        $this->data[$key] = $values;
    }

    /**
     * @return array<string, int|string>
     */
    public function hGetAll(string $key): array
    {
        $value = $this->data[$key] ?? [];

        return is_array($value) ? $value : [];
    }

    public function ttl(string $key): int
    {
        if (! isset($this->ttlMap[$key])) {
            return -1; // Redis behavior: no expire
        }

        $ttl = $this->ttlMap[$key] - time();

        return $ttl > 0 ? $ttl : 0;
    }

    /**
     * @return array<string, bool>
     */
    public function info(): array
    {
        return ['fake' => true];
    }
}
