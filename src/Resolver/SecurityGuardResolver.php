<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:09
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Resolver;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Drivers\RedisSecurityGuard;
use Maatify\SecurityGuard\Drivers\MySQL\MySQLSecurityGuard;
use Maatify\SecurityGuard\Drivers\Mongo\MongoSecurityGuard;
use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;
use Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface;
use MongoDB\Database as MongoDatabase;
use PDO;
use Predis\Client as PredisClient;
use Redis;
use Doctrine\DBAL\Connection as DbalConnection;
use RuntimeException;

/**
 * 🔍 SecurityGuardResolver
 *
 * Automatically determines which driver implementation should be used
 * based on the underlying connection type.
 *
 * This abstraction removes complexity from the application layer by
 * routing all requests to the correct SecurityGuard driver (Redis, MySQL, Mongo, etc.)
 * without exposing any storage-specific details.
 */
final class SecurityGuardResolver
{
    public function resolve(
        AdapterInterface $adapter,
        IdentifierStrategyInterface $strategy
    ): SecurityGuardDriverInterface {
        /** @var mixed $driver */
        $driver = $adapter->getDriver();

        // Redis: native
        if ($driver instanceof Redis) {
            return new RedisSecurityGuard($adapter, $strategy);
        }

        // Redis: Predis
        if ($driver instanceof PredisClient) {
            return new RedisSecurityGuard($adapter, $strategy);
        }

        // MySQL: PDO
        if ($driver instanceof PDO) {
            return new MySQLSecurityGuard($adapter, $strategy);
        }

        // MySQL: Doctrine DBAL
        if ($driver instanceof DbalConnection) {
            return new MySQLSecurityGuard($adapter, $strategy);
        }

        // MongoDB
        if ($driver instanceof MongoDatabase) {
            return new MongoSecurityGuard($adapter, $strategy);
        }

        throw new RuntimeException(
            'Unsupported SecurityGuard driver type: ' . get_debug_type($driver)
        );
    }
}
