<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:06
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Fake;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\Common\Contracts\Adapter\KeyValueAdapterInterface;
use Predis\Client;

final class FakeAdapter implements AdapterInterface, KeyValueAdapterInterface
{
    /** @var array<string,string> */
    private array $store = [];

    /** @var array<string,int> */
    private array $ttl = [];

    // ------------------------------------------------------------------
    // AdapterInterface (dummy for constructor compatibility)
    // ------------------------------------------------------------------

    public function connect(): void
    {
    }
    public function disconnect(): void
    {
    }
    public function isConnected(): bool
    {
        return true;
    }
    public function healthCheck(): bool
    {
        return true;
    }
    public function getDriver(): \Predis\Client
    {
        return new Client(); // Fake Predis for RedisSecurityGuard ONLY
    }

    public function getConnection(): \Predis\Client
    {
        return $this->getDriver();
    }

    // ------------------------------------------------------------------
    // ✅ KeyValueAdapterInterface (REAL IMPLEMENTATION)
    // ------------------------------------------------------------------

    public function get(string $key): string|null
    {
        if (!$this->exists($key)) {
            return null;
        }

        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        if (is_scalar($value)) {
            $this->store[$key] = (string) $value;
        } else {
            $encoded = json_encode($value);
            $this->store[$key] = is_string($encoded) ? $encoded : '';
        }

        if ($ttl !== null) {
            $this->ttl[$key] = time() + $ttl;
        }
    }

    public function del(string $key): void
    {
        unset($this->store[$key], $this->ttl[$key]);
    }

    /** @return array<int,string> */
    public function keys(string $pattern): array
    {
        $regex = '/^' . str_replace(['*'], ['.*'], preg_quote($pattern, '/')) . '$/';

        return array_values(
            array_filter(
                array_keys($this->store),
                static fn (string $key): bool => (bool) preg_match($regex, $key)
            )
        );
    }

    // ------------------------------------------------------------------
    // TTL Simulation (Private Helper)
    // ------------------------------------------------------------------

    private function exists(string $key): bool
    {
        if (!array_key_exists($key, $this->store)) {
            return false;
        }

        if (!array_key_exists($key, $this->ttl)) {
            return true;
        }

        // ✅ TTL MUST EXPIRE ON <= NOT <
        if ($this->ttl[$key] <= time()) {
            unset($this->store[$key], $this->ttl[$key]);
            return false;
        }

        return true;
    }

}
