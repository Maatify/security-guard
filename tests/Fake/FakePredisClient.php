<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:45
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Predis;

/**
 * Lightweight fully-typed fake Predis client for testing.
 */
class Client
{
    /** @var array<string, int> */
    private array $strings = [];

    /** @var array<string, array<string, int|string>> */
    private array $hashes = [];

    /** @var array<string, int> */
    private array $expires = [];

    /**
     * @param array<string, mixed> $config
     */
    /** @phpstan-ignore-next-line */
    public function __construct(array $config = [])
    {
        // config intentionally unused (Predis compatible)
    }

    // -------------------------------------------------------------
    // STRING OPERATIONS
    // -------------------------------------------------------------

    public function incr(string $key): int
    {
        $current = $this->strings[$key] ?? 0;
        $current++;
        $this->strings[$key] = $current;

        return $current;
    }

    public function get(string $key): int|string|null
    {
        return $this->strings[$key] ?? null;
    }

    public function expire(string $key, int $ttl): void
    {
        $this->expires[$key] = time() + $ttl;
    }

    public function ttl(string $key): int
    {
        if (!isset($this->expires[$key])) {
            return -1;
        }

        $diff = $this->expires[$key] - time();
        return $diff > 0 ? $diff : 0;
    }

    // -------------------------------------------------------------
    // HASH OPERATIONS
    // -------------------------------------------------------------

    /**
     * @param array<string, mixed> $data
     */
    public function hMSet(string $key, array $data): void
    {
        $normalized = [];

        foreach ($data as $k => $v) {
            if (is_int($v) || is_string($v)) {
                $normalized[$k] = $v;
            } elseif (is_bool($v)) {
                $normalized[$k] = $v ? 1 : 0;
            } elseif (is_float($v)) {
                $normalized[$k] = (string)$v;
            } elseif ($v === null) {
                $normalized[$k] = '';
            } else {
                // fallback string cast
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unsupported Redis hash value type for key "%s": %s',
                        $k,
                        gettype($v)
                    )
                );
            }
        }

        $this->hashes[$key] = $normalized;
    }


    /**
     * @return array<string, string|int>
     */
    public function hGetAll(string $key): array
    {
        $data = $this->hashes[$key] ?? [];

        foreach ($data as $k => $v) {
            if (is_numeric($v)) {
                $data[$k] = (int)$v;
            }
        }

        return $data;
    }

    // -------------------------------------------------------------
    // DELETE
    // -------------------------------------------------------------

    /**
     * @param string|string[] $keys
     */
    public function del(string|array $keys): void
    {
        $keys = (array)$keys;

        foreach ($keys as $key) {
            unset($this->strings[$key], $this->hashes[$key], $this->expires[$key]);
        }
    }

    // -------------------------------------------------------------
    // INFO
    // -------------------------------------------------------------

    /**
     * @return array<string, int|bool>
     */
    public function info(): array
    {
        return [
            'fake_redis' => true,
            'keys' => count($this->strings) + count($this->hashes),
        ];
    }
}
