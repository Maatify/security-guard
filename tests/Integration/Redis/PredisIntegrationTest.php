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

                // PHPStan might see $_ENV values as mixed
                $portEnv = $_ENV['REDIS_PORT'] ?? 6379;
                $port = is_numeric($portEnv) ? (int)$portEnv : 6379;

                $this->client = new Client([
                    'scheme' => 'tcp',
                    'host'   => (string)$host,
                    'port'   => $port,
                ]);

                try {
                    // Predis connects lazily, so we force a check
                    $this->client->connect();
                    // Connection status via ConnectionInterface
                    /** @var ConnectionInterface $connection */
                    $connection = $this->client->getConnection();
                    $this->connected = (bool)$connection->isConnected();
                } catch (\Throwable) {
                    $this->connected = false;
                }
            }

            public function connect(): void
            {
                if (! $this->connected) {
                    try {
                        $this->client->connect();
                        /** @var ConnectionInterface $connection */
                        $connection = $this->client->getConnection();
                        $this->connected = (bool)$connection->isConnected();
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
                try {
                    /** @var ConnectionInterface $connection */
                    $connection = $this->client->getConnection();
                    return $this->connected && (bool)$connection->isConnected();
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
