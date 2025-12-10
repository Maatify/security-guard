<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers\Support;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Redis;

/**
 * RealRedisAdapter
 *
 * Provides a real Redis connection for tests that require integration.
 */
final class RealRedisAdapter implements AdapterInterface
{
    private Redis $redis;
    private bool $connected = false;

    public function __construct()
    {
        $this->redis = new Redis();
        try {
            // Attempt to connect to localhost standard port.
            // Using @ to suppress warnings if redis is not running - let tests decide what to do.
            $this->connected = @$this->redis->connect('127.0.0.1', 6379);
        } catch (\Throwable) {
            $this->connected = false;
        }
    }

    public function connect(): void
    {
        if (! $this->connected) {
            try {
                $this->connected = @$this->redis->connect('127.0.0.1', 6379);
            } catch (\Throwable) {
                $this->connected = false;
            }
        }
    }

    public function disconnect(): void
    {
        if ($this->connected) {
            $this->redis->close();
            $this->connected = false;
        }
    }

    public function isConnected(): bool
    {
        try {
            return $this->connected && $this->redis->ping();
        } catch (\Throwable) {
            return false;
        }
    }

    public function healthCheck(): bool
    {
        return $this->isConnected();
    }

    /**
     * @return object
     */
    public function getDriver(): object
    {
        return $this->redis;
    }

    /**
     * @return object
     */
    public function getConnection(): object
    {
        return $this->redis;
    }
}
