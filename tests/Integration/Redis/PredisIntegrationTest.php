<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration\Redis;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Predis\Client;
use Predis\Connection\ConnectionInterface;

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
                $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
                $port = (int)((string)($_ENV['REDIS_PORT'] ?? '6379'));

                $this->client = new Client([
                    'scheme' => 'tcp',
                    'host'   => $host,
                    'port'   => $port,
                ]);

                try {
                    // Predis connects lazily, so we force a check
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
