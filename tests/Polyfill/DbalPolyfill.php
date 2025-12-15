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

namespace Doctrine\DBAL;

if (!class_exists('Doctrine\DBAL\Connection')) {
    /**
     * Polyfill for Doctrine DBAL Connection for PHPStan analysis.
     */
    class Connection
    {
        /**
         * @param array<string, mixed> $data
         * @param array<string, mixed> $types
         */
        public function insert(string $table, array $data, array $types = []): int|string
        {
            return 1;
        }

        /**
         * @param array<mixed> $params
         * @param array<int|string, mixed> $types
         * @return array<string, mixed>|false
         */
        public function fetchAssociative(string $sql, array $params = [], array $types = []): array|false
        {
            return [];
        }

        /**
         * @param array<string, mixed> $criteria
         * @param array<string, mixed> $types
         */
        public function delete(string $table, array $criteria, array $types = []): int|string
        {
            return 1;
        }

        /**
         * @param array<mixed> $params
         * @param array<int|string, mixed> $types
         */
        public function executeStatement(string $sql, array $params = [], array $types = []): int|string
        {
            return 1;
        }

        /**
         * @param array<mixed> $params
         * @param array<int|string, mixed> $types
         */
        public function fetchOne(string $sql, array $params = [], array $types = []): mixed
        {
            return 1;
        }
    }
}
