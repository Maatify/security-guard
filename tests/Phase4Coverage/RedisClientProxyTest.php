<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:03
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace {
    // Polyfill for Redis if extension is missing
    if (!class_exists('Redis')) {
        /**
         * @mixin \Redis
         */
        class Redis
        {
            public function incr(string $key): int|bool { return 1; }
            public function expire(string $key, int $seconds): bool { return true; }
            public function hGetAll(string $key): array|false { return []; }
            public function hMSet(string $key, array $data): bool { return true; }
            /**
             * @param string|string[] $key
             * @param string ...$other_keys
             * @return int
             */
            public function del(string|array $key, ...$other_keys): int { return 1; }
            public function ttl(string $key): int { return -1; }
            public function info(?string $option = null): array|false { return []; }
        }
    }
}

namespace Maatify\SecurityGuard\Tests\Phase4Coverage {

    use Maatify\SecurityGuard\Drivers\Support\RedisClientProxy;
    use PHPUnit\Framework\TestCase;
    use Predis\Client as PredisClient;
    use Redis;

    class RedisClientProxyTest extends TestCase
    {
        /**
         * @return array<string, mixed>
         */
        public function redisProvider(): array
        {
            return [
                'redis' => ['redis'],
            ];
        }

        public function testIncrWithRedis(): void
        {
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('incr')
                ->with('key')
                ->willReturn(5);

            $proxy = new RedisClientProxy($redis);
            $this->assertSame(5, $proxy->incr('key'));
        }

        public function testIncrWithPredis(): void
        {
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('incr')
                ->with('key')
                ->willReturn(5);

            $proxy = new RedisClientProxy($predis);
            $this->assertSame(5, $proxy->incr('key'));
        }

        public function testExpireWithRedis(): void
        {
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('expire')
                ->with('key', 3600)
                ->willReturn(true);

            $proxy = new RedisClientProxy($redis);
            $proxy->expire('key', 3600);
        }

        public function testExpireWithPredis(): void
        {
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('expire')
                ->with('key', 3600);
            // Predis expire is void, do not expect return value

            $proxy = new RedisClientProxy($predis);
            $proxy->expire('key', 3600);
        }

        public function testHGetAllWithRedisSuccess(): void
        {
            $data = ['field' => 'value'];
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('hGetAll')
                ->with('key')
                ->willReturn($data);

            $proxy = new RedisClientProxy($redis);
            $this->assertSame($data, $proxy->hGetAll('key'));
        }

        public function testHGetAllWithRedisFailure(): void
        {
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('hGetAll')
                ->with('key')
                ->willReturn(false);

            $proxy = new RedisClientProxy($redis);
            $this->assertFalse($proxy->hGetAll('key'));
        }

        public function testHGetAllWithPredisSuccess(): void
        {
            $data = ['field' => 'value'];
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('hgetall')
                ->with('key')
                ->willReturn($data);

            $proxy = new RedisClientProxy($predis);
            $this->assertSame($data, $proxy->hGetAll('key'));
        }

        // testHGetAllWithPredisNull removed as Predis\Client::hgetall has strictly typed array return

        public function testHGetAllWithNormalization(): void
        {
            // Predis might return mixed types, verify normalization to strings
            $data = ['int' => 123, 'str' => 'val'];
            $expected = ['int' => '123', 'str' => 'val'];

            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('hgetall')
                ->with('key')
                ->willReturn($data);

            $proxy = new RedisClientProxy($predis);
            $this->assertSame($expected, $proxy->hGetAll('key'));
        }

        public function testHMSetWithRedis(): void
        {
            $data = ['field' => 'value'];
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('hMSet')
                ->with('key', $data)
                ->willReturn(true);

            $proxy = new RedisClientProxy($redis);
            $proxy->hMSet('key', $data);
        }

        public function testHMSetWithPredis(): void
        {
            $data = ['field' => 'value'];
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('hmset')
                ->with('key', $data);
            // Predis hmset is void, do not expect return value

            $proxy = new RedisClientProxy($predis);
            $proxy->hMSet('key', $data);
        }

        public function testDelWithRedis(): void
        {
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('del')
                ->with('key')
                ->willReturn(1);

            $proxy = new RedisClientProxy($redis);
            $proxy->del('key');
        }

        public function testDelWithPredis(): void
        {
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('del')
                ->with(['key']); // Predis expects array or variadic, proxy passes array
            // Predis del is void, do not expect return value

            $proxy = new RedisClientProxy($predis);
            $proxy->del('key');
        }

        public function testTtlWithRedis(): void
        {
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('ttl')
                ->with('key')
                ->willReturn(100);

            $proxy = new RedisClientProxy($redis);
            $this->assertSame(100, $proxy->ttl('key'));
        }

        public function testTtlWithPredis(): void
        {
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('ttl')
                ->with('key')
                ->willReturn(100);

            $proxy = new RedisClientProxy($predis);
            $this->assertSame(100, $proxy->ttl('key'));
        }

        public function testInfoWithRedisSuccess(): void
        {
            $info = ['redis_version' => '6.0.0'];
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('info')
                ->willReturn($info);

            $proxy = new RedisClientProxy($redis);
            $this->assertSame($info, $proxy->info());
        }

        public function testInfoWithRedisFailure(): void
        {
            $redis = $this->createMock(Redis::class);
            $redis->expects($this->once())
                ->method('info')
                ->willReturn(false);

            $proxy = new RedisClientProxy($redis);
            $this->assertSame([], $proxy->info());
        }

        public function testInfoWithPredisSuccess(): void
        {
            $info = ['redis_version' => '6.0.0'];
            $predis = $this->createMock(PredisClient::class);
            $predis->expects($this->once())
                ->method('info')
                ->willReturn($info);

            $proxy = new RedisClientProxy($predis);
            $this->assertSame($info, $proxy->info());
        }
    }
}
