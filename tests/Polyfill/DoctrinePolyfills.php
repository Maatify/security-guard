<?php

declare(strict_types=1);

namespace Doctrine\DBAL {
    if (!class_exists('Doctrine\DBAL\Connection')) {
        class Connection
        {
            public function insert(string $table, array $data, array $types = []): int|string {
                return 1;
            }

            public function fetchAssociative(string $query, array $params = [], array $types = []): array|false {
                return [];
            }

            public function delete(string $table, array $criteria, array $types = []): int|string {
                return 1;
            }

            public function executeStatement(string $sql, array $params = [], array $types = []): int|string {
                return 1;
            }

            public function fetchOne(string $sql, array $params = [], array $types = []): mixed {
                return false;
            }
        }
    }
}
