<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 09:35
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Fake;

use Maatify\Common\Contracts\Adapter\KeyValueAdapterInterface;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Predis\Client;

final class FakeKeyValueAdapter implements AdapterInterface, KeyValueAdapterInterface
{
    /** @var array<string,string|null> */
    private array $storage = [];

    // -------------------------------------------------------------------------
    // ✅ KeyValueAdapterInterface
    // -------------------------------------------------------------------------

    public function get(string $key): string|null
    {
        return $this->storage[$key] ?? null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $encoded = is_scalar($value) ? (string)$value : json_encode($value);

        $this->storage[$key] = is_string($encoded) ? $encoded : null;
    }

    public function del(string $key): void
    {
        unset($this->storage[$key]);
    }

    /**
     * @return array<int,string>
     */
    public function keys(string $pattern): array
    {
        return array_keys($this->storage);
    }

    // -------------------------------------------------------------------------
    // ✅ AdapterInterface (Stub for tests)
    // -------------------------------------------------------------------------

    public function connect(): void {}

    public function disconnect(): void {}

    public function isConnected(): bool
    {
        return true;
    }

    public function healthCheck(): bool
    {
        return true;
    }

    public function getDriver(): Client
    {
        return new Client();
    }

    public function getConnection(): Client
    {
        return new Client();
    }
}
