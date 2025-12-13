<?php

declare(strict_types=1);

// -----------------------------------------------------------------------------
//  Polyfills for missing extensions/libraries in the test environment
// -----------------------------------------------------------------------------

namespace {
    if (!class_exists('Redis')) {
        class Redis {}
    }
    if (!class_exists('PDO')) {
        class PDO {}
    }
}

namespace Doctrine\DBAL {
    if (!class_exists('Connection')) {
        class Connection {}
    }
}

namespace MongoDB {
    if (!class_exists('Database')) {
        class Database {
            public function selectCollection($name, array $options = []) {}
        }
    }
    if (!class_exists('Collection')) {
        class Collection {
            public function createIndex($keys, $options = []) {}

            public function countDocuments($filter = [], array $options = []): int { return 0; }

            public function insertOne($document, array $options = []) {}

            public function findOne($filter = [], array $options = []) { return null; }

            public function updateOne($filter, $update, array $options = []) {}

            public function deleteOne($filter, array $options = []) {}

            public function deleteMany($filter, array $options = []) {}
        }
    }
}

namespace Maatify\SecurityGuard\Tests\Resolver {

    use Maatify\Common\Contracts\Adapter\AdapterInterface;
    use Maatify\SecurityGuard\Drivers\Mongo\MongoSecurityGuard;
    use Maatify\SecurityGuard\Drivers\MySQL\MySQLSecurityGuard;
    use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
    use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;
    use Maatify\SecurityGuard\Resolver\SecurityGuardResolver;
    use PHPUnit\Framework\TestCase;

    class SecurityGuardResolverTest extends TestCase
    {
        private SecurityGuardResolver $resolver;

        protected function setUp(): void
        {
            $this->resolver = new SecurityGuardResolver();
        }

        public function testResolveRedisNative(): void
        {
            $adapter = $this->createMock(AdapterInterface::class);
            $strategy = $this->createMock(IdentifierStrategyInterface::class);

            $redis = $this->getMockBuilder(\Redis::class)
                          ->disableOriginalConstructor()
                          ->getMock();

            $adapter->expects($this->atLeastOnce())
                ->method('getDriver')
                ->willReturn($redis);

            $guard = $this->resolver->resolve($adapter, $strategy);

            $this->assertInstanceOf(RedisSecurityGuard::class, $guard);
        }

        public function testResolveRedisPredis(): void
        {
            $adapter = $this->createMock(AdapterInterface::class);
            $strategy = $this->createMock(IdentifierStrategyInterface::class);

            if (!class_exists(\Predis\Client::class)) {
                $this->markTestSkipped('Predis\Client class not found');
            }

            $predis = $this->getMockBuilder(\Predis\Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();

            $adapter->expects($this->atLeastOnce())
                ->method('getDriver')
                ->willReturn($predis);

            $guard = $this->resolver->resolve($adapter, $strategy);

            $this->assertInstanceOf(RedisSecurityGuard::class, $guard);
        }

        public function testResolveMySQLPDO(): void
        {
            $adapter = $this->createMock(AdapterInterface::class);
            $strategy = $this->createMock(IdentifierStrategyInterface::class);

            $pdo = $this->getMockBuilder(\PDO::class)
                        ->disableOriginalConstructor()
                        ->getMock();

            $adapter->expects($this->atLeastOnce())
                ->method('getDriver')
                ->willReturn($pdo);

            $guard = $this->resolver->resolve($adapter, $strategy);

            $this->assertInstanceOf(MySQLSecurityGuard::class, $guard);
        }

        public function testResolveMySQLDoctrine(): void
        {
            $adapter = $this->createMock(AdapterInterface::class);
            $strategy = $this->createMock(IdentifierStrategyInterface::class);

            $dbal = $this->getMockBuilder(\Doctrine\DBAL\Connection::class)
                         ->disableOriginalConstructor()
                         ->getMock();

            $adapter->expects($this->atLeastOnce())
                ->method('getDriver')
                ->willReturn($dbal);

            $guard = $this->resolver->resolve($adapter, $strategy);

            $this->assertInstanceOf(MySQLSecurityGuard::class, $guard);
        }

        public function testResolveMongo(): void
        {
            $adapter = $this->createMock(AdapterInterface::class);
            $strategy = $this->createMock(IdentifierStrategyInterface::class);

            $mongoDb = $this->getMockBuilder(\MongoDB\Database::class)
                            ->disableOriginalConstructor()
                            ->getMock();

            $collection = $this->getMockBuilder(\MongoDB\Collection::class)
                               ->disableOriginalConstructor()
                               ->getMock();

            $mongoDb->method('selectCollection')->willReturn($collection);

            $adapter->expects($this->atLeastOnce())
                ->method('getDriver')
                ->willReturn($mongoDb);

            $guard = $this->resolver->resolve($adapter, $strategy);

            $this->assertInstanceOf(MongoSecurityGuard::class, $guard);
        }

        public function testResolveUnsupported(): void
        {
            $adapter = $this->createMock(AdapterInterface::class);
            $strategy = $this->createMock(IdentifierStrategyInterface::class);

            $adapter->expects($this->atLeastOnce())
                ->method('getDriver')
                ->willReturn(new \stdClass());

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unsupported SecurityGuard driver type');

            $this->resolver->resolve($adapter, $strategy);
        }
    }
}
