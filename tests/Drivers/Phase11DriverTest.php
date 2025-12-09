<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use PHPUnit\Framework\TestCase;

class Phase11DriverTest extends TestCase
{
    public function testDriverWithoutKeyValueAdapterThrows(): void
    {
        // Create an anonymous class that implements AdapterInterface but NOT KeyValueAdapterInterface
        $badAdapter = new class implements AdapterInterface {
            public function connect(): void {}
            public function disconnect(): void {}
            public function isConnected(): bool { return true; }
            public function healthCheck(): bool { return true; }
            public function getDriver(): object { return new \stdClass(); }
            public function getConnection(): object { return new \stdClass(); }
        };

        $driver = new FakeSecurityGuardDriver($badAdapter, new FakeIdentifierStrategy());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('FakeSecurityGuardDriver requires KeyValueAdapterInterface');

        // Trigger a method that calls kv()
        $driver->resetAttempts('1.1.1.1', 'user');
    }
}
