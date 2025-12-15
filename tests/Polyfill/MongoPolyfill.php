<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-24 10:45
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace MongoDB;

if (!class_exists('MongoDB\Database')) {
    /**
     * Polyfill for MongoDB\Database for PHPStan analysis.
     */
    class Database {
        /**
         * @param string               $name
         * @param array<string, mixed> $options
         */
        public function selectCollection(string $name, array $options = []): Collection {
            return new Collection();
        }
    }
}

if (!class_exists('MongoDB\Collection')) {
    /**
     * Polyfill for MongoDB\Collection for PHPStan analysis.
     */
    class Collection {
        /**
         * @param mixed                $keys
         * @param array<string, mixed> $options
         */
        public function createIndex(mixed $keys, array $options = []): mixed {
            return '';
        }

        /**
         * @param mixed                $filter
         * @param array<string, mixed> $options
         */
        public function countDocuments(mixed $filter = [], array $options = []): int {
            return 0;
        }

        /**
         * @param mixed                $document
         * @param array<string, mixed> $options
         */
        public function insertOne(mixed $document, array $options = []): mixed {
            return null;
        }

        /**
         * @param mixed                $filter
         * @param array<string, mixed> $options
         * @return array<string, mixed>|object|null
         */
        public function findOne(mixed $filter = [], array $options = []): mixed {
            return null;
        }

        /**
         * @param mixed                $filter
         * @param mixed                $update
         * @param array<string, mixed> $options
         */
        public function updateOne(mixed $filter, mixed $update, array $options = []): mixed {
            return null;
        }

        /**
         * @param mixed                $filter
         * @param array<string, mixed> $options
         */
        public function deleteOne(mixed $filter, array $options = []): mixed {
            return null;
        }

        /**
         * @param mixed                $filter
         * @param array<string, mixed> $options
         */
        public function deleteMany(mixed $filter, array $options = []): mixed {
            return null;
        }
    }
}
