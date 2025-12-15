<?php

/**
 * @deprecated
 * Legacy integration test.
 * Superseded by IntegrationV2 (tests/IntegrationV2).
 * Do not extend, modify, or rely on this test.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration\Redis;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Predis\Client;
use Predis\Connection\ConnectionInterface;

/**
 * Interface to help PHPStan understand Predis Client methods that might be magic or missing in stubs.
 * @mixin Client
 */
interface PredisClientProxyInterface
{
    public function connect(): void;
    public function disconnect(): void;
    public function getConnection(): ConnectionInterface;
    public function isConnected(): bool;
}

class PredisIntegrationTest extends AbstractRedisTestCase
{
    protected function setUp(): void
    {
        // Require predis/predis package
        if (! class_exists(Client::class)) {
            $this->markTestSkipped('predis/predis is not installed.');
        }
        parent::setUp();
    }

    protected function createAdapter(): AdapterInterface
    {
        return new class implements AdapterInterface {
            private Client $client;
            private bool $connected = false;

            public function __construct()
            {
                $hostEnv = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
                $host = is_string($hostEnv) ? $hostEnv : '127.0.0.1';

                $portEnv = $_ENV['REDIS_PORT'] ?? 6379;
                $port = is_numeric($portEnv) ? (int)$portEnv : 6379;

                $this->client = new Client([
                    'scheme' => 'tcp',
                    'host'   => $host,
                    'port'   => $port,
                ]);

                try {
                    // Predis connects lazily, so we force a check
                    $this->proxy()->connect();
                    $this->connected = (bool)$this->proxy()->getConnection()->isConnected();
                } catch (\Throwable) {
                    $this->connected = false;
                }
            }

            /**
             * Helper to make PHPStan happy about Predis methods
             * @return PredisClientProxyInterface&Client
             */
            private function proxy(): object
            {
                /** @var PredisClientProxyInterface&Client $client */
                $client = $this->client;
                return $client;
            }

            public function connect(): void
            {
                if (! $this->connected) {
                    try {
                        $this->proxy()->connect();
                        $this->connected = (bool)$this->proxy()->getConnection()->isConnected();
                    } catch (\Throwable) {
                        $this->connected = false;
                    }
                }
            }

            public function disconnect(): void
            {
                if ($this->connected) {
                    $this->proxy()->disconnect();
                    $this->connected = false;
                }
            }

            public function isConnected(): bool
            {
                try {
                    return $this->connected && (bool)$this->proxy()->getConnection()->isConnected();
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
                return $this->client;
            }

            /**
             * @return object
             */
            public function getConnection(): object
            {
                return $this->client;
            }
        };
    }
}
