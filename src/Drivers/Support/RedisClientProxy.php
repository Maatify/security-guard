<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:03
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers\Support;

use Predis\Client as PredisClient;
use Redis;

/**
 * 🧩 RedisClientProxy
 *
 * Unified Redis access layer that supports both:
 * - phpredis (Redis extension)
 * - Predis\Client
 *
 * By abstracting the differences, all Redis-based drivers
 * can operate without caring about a connection type.
 *
 * @package Maatify\SecurityGuard\Drivers\Support
 */
class RedisClientProxy
{
    /**
     * @var Redis|PredisClient
     */
    private Redis|PredisClient $client;

    /**
     * @param Redis|PredisClient $client
     */
    public function __construct(Redis|PredisClient $client)
    {
        $this->client = $client;
    }

    // -------------------------------------------------------------------------
    //  Numeric Counters
    // -------------------------------------------------------------------------

    public function incr(string $key): int
    {
        /** @var int $value */
        $value = $this->client->incr($key);
        return $value;
    }

    // -------------------------------------------------------------------------
    //  Expiration
    // -------------------------------------------------------------------------

    public function expire(string $key, int $seconds): void
    {
        $this->client->expire($key, $seconds);
    }

    // -------------------------------------------------------------------------
    //  Hash Operations
    // -------------------------------------------------------------------------

    /**
     * @return array<string,string>|false
     */
    public function hGetAll(string $key): array|false
    {
        if ($this->client instanceof Redis) {
            $result = $this->client->hGetAll($key);
        } else {
            /** @var PredisClient $predis */
            $predis = $this->client;
            /** @var array<string,string>|null $tmp */
            $tmp = $predis->hgetall($key);
            $result = $tmp ?? [];
        }

        if (!is_array($result)) {
            return false;
        }

        /** @var array<string,string> $normalized */
        $normalized = [];

        foreach ($result as $k => $v) {
            $normalized[(string)$k] = (string)$v;
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function hMSet(string $key, array $data): void
    {
        if ($this->client instanceof Redis) {
            $this->client->hMSet($key, $data);
        } else {
            /** @var PredisClient $predis */
            $predis = $this->client;
            $predis->hmset($key, $data);
        }
    }

    // -------------------------------------------------------------------------
    //  Deletion
    // -------------------------------------------------------------------------

    public function del(string $key): void
    {
        if ($this->client instanceof Redis) {
            $this->client->del($key);
        } else {
            /** @var PredisClient $predis */
            $predis = $this->client;
            $predis->del([$key]);
        }
    }

    // -------------------------------------------------------------------------
    //  TTL
    // -------------------------------------------------------------------------

    public function ttl(string $key): int
    {
        /** @var int $ttl */
        $ttl = $this->client->ttl($key);
        return $ttl;
    }

    // -------------------------------------------------------------------------
    //  Server Info
    // -------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     */
    public function info(): array
    {
        $raw = $this->client->info();

        if (!is_array($raw)) {
            /** @var array<string,mixed> $empty */
            $empty = [];
            return $empty;
        }

        /** @var array<string,mixed> $out */
        $out = $raw;
        return $out;
    }
}
