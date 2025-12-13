<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration\Redis;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Predis\Client;

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
                // We use defaults or ENV. Predis client handles ENV automatically if configured,
                // but here we just pass simple parameters or let it default.
                // However, BaseIntegrationTestCase has requireEnv('REDIS_HOST').
                // We should use it.
                $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
                $port = $_ENV['REDIS_PORT'] ?? 6379;

                $this->client = new Client([
                    'scheme' => 'tcp',
                    'host'   => $host,
                    'port'   => (int)$port,
                ]);

                try {
                    $this->client->connect();
                    $this->connected = $this->client->isConnected();
                } catch (\Throwable) {
                    $this->connected = false;
                }
            }

            public function connect(): void
            {
                if (! $this->connected) {
                    try {
                        $this->client->connect();
                        $this->connected = $this->client->isConnected();
                    } catch (\Throwable) {
                        $this->connected = false;
                    }
                }
            }

            public function disconnect(): void
            {
                if ($this->connected) {
                    $this->client->disconnect();
                    $this->connected = false;
                }
            }

            public function isConnected(): bool
            {
                return $this->connected && $this->client->isConnected();
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
