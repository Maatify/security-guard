<?php

declare(strict_types=1);

namespace {
    if (!class_exists('Redis')) {
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

            public function ping(?string $message = null): mixed
            {
                return $message ?? true;
            }

            public function incr(string $key): int|false
            {
                return 1;
            }

            public function expire(string $key, int $ttl): bool
            {
                return true;
            }

            /**
             * @return array<string, string>|false
             */
            public function hGetAll(string $key): array|false
            {
                return [];
            }

            public function hMSet(string $key, array $value): bool
            {
                return true;
            }

            public function del(string|array $key): int|false
            {
                return 1;
            }

            public function ttl(string $key): int|false
            {
                return 10;
            }

            /**
             * @return array<string, mixed>|false
             */
            public function info(?string $option = null): array|false
            {
                return [];
            }

            public function flushAll(?bool $async = null): bool
            {
                return true;
            }
        }
    }

    if (!class_exists('PDO')) {
        class PDO
        {
            public const FETCH_ASSOC = 2;

            public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null) {}

            public function prepare(string $query, array $options = []): PDOStatement|false {
                return new PDOStatement();
            }

            public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
                return new PDOStatement();
            }

            public function exec(string $statement): int|false {
                return 0;
            }

            public function lastInsertId(?string $name = null): string|false {
                return '0';
            }
        }
    }

    if (!class_exists('PDOStatement')) {
        class PDOStatement
        {
            public function execute(?array $params = null): bool {
                return true;
            }

            public function fetch(int $mode = PDO::FETCH_BOTH, int $cursorOrientation = 0, int $cursorOffset = 0): mixed {
                return false;
            }

            public function fetchAll(int $mode = PDO::FETCH_BOTH, mixed ...$args): array {
                return [];
            }

            public function fetchColumn(int $column = 0): mixed {
                return false;
            }
        }
    }
}
