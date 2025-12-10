<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use PHPUnit\Framework\TestCase;

class Phase3G11DriverTest extends TestCase
{
    public function testDriverWithoutKeyValueAdapterThrows(): void
    {
        // Create an anonymous class that implements AdapterInterface but NOT KeyValueAdapterInterface
        $badAdapter = new class () implements AdapterInterface {
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
            /**
             * Return a safe standard object (PDO) that is guaranteed to be in the union type
             * but is NOT a KeyValue-compatible client. This avoids dependency on 'predis'
             * or 'redis' extension being present for this specific test.
             *
             * @return \PDO
             */
            public function getDriver(): object
            {
                return new \PDO('sqlite::memory:');
            }
            /**
             * @return \PDO
             */
            public function getConnection(): object
            {
                return new \PDO('sqlite::memory:');
            }
        };

        $driver = new FakeSecurityGuardDriver($badAdapter, new FakeIdentifierStrategy());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('FakeSecurityGuardDriver requires KeyValueAdapterInterface');

        // Trigger a method that calls kv()
        $driver->resetAttempts('1.1.1.1', 'user');
    }
}
