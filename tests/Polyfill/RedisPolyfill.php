<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-24 10:30
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

if (!class_exists('Redis')) {
    /**
     * Polyfill for Redis extension class for PHPStan analysis.
     */
    class Redis
    {
        public function connect(string $host, int $port = 6379, float $timeout = 0.0, mixed $reserved = null, int $retry_interval = 0, float $read_timeout = 0.0): bool
        {
            return true;
        }

        public function close(): bool
        {
            return true;
        }

        public function ping(mixed $message = null): mixed
        {
            return true;
        }

        public function flushAll(mixed $async = null): bool
        {
            return true;
        }

        public function incr(string $key): int
        {
            return 1;
        }

        public function expire(string $key, int $ttl): bool
        {
            return true;
        }

        public function ttl(string $key): int
        {
            return 1;
        }

        /**
         * @param string|string[] $key
         * @return int
         */
        public function del(string|array $key): int
        {
            return 1;
        }

        /**
         * @return array<string, string>|false
         */
        public function hGetAll(string $key): array|false
        {
            return [];
        }

        /**
         * @param array<string, mixed> $dictionary
         */
        public function hMSet(string $key, array $dictionary): bool
        {
            return true;
        }

        /**
         * @return array<string, mixed>|false
         */
        public function info(mixed $option = null): array|false
        {
            return [];
        }
    }
}
