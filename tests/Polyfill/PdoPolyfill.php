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

if (!class_exists('PDO')) {
    /**
     * Polyfill for PDO class for PHPStan analysis.
     */
    class PDO
    {
        public const FETCH_ASSOC = 2;

        public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
        {
        }

        public function prepare(string $query, array $options = []): PDOStatement|false
        {
            return new PDOStatement();
        }

        public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
        {
            return new PDOStatement();
        }
    }
}

if (!class_exists('PDOStatement')) {
    /**
     * Polyfill for PDOStatement class for PHPStan analysis.
     */
    class PDOStatement
    {
        public function execute(?array $params = null): bool
        {
            return true;
        }

        public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
        {
            return [];
        }

        public function fetchColumn(int $column = 0): mixed
        {
            return 1;
        }
    }
}
