<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:35
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers\MySQL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver;
use Maatify\SecurityGuard\Drivers\MySQL\Contracts\MySQLDriverInterface;
use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;
use PDO;
use RuntimeException;
use Throwable;

/**
 * 🧩 MySQLSecurityGuard
 *
 * High-level security guard driver for MySQL.
 * Chooses the proper low-level implementation based on the underlying driver:
 * - PdoMySQLDriver   for native PDO
 * - DbalMySQLDriver  for Doctrine DBAL Connection
 *
 * ✅ لا يحتوي على أي SQL
 * كل الـ SQL logic موجود داخل:
 * - PdoMySQLDriver
 * - DbalMySQLDriver
 */
final class MySQLSecurityGuard extends AbstractSecurityGuardDriver
{
    private MySQLDriverInterface $driver;

    public function __construct(
        AdapterInterface $adapter,
        IdentifierStrategyInterface $strategy
    ) {
        parent::__construct($adapter, $strategy);

        $this->assertConnected($adapter);

        $raw = $adapter->getDriver();

        if ($raw instanceof PDO) {
            $this->driver = new PdoMySQLDriver($adapter);
        } elseif ($raw instanceof Connection) {
            $this->driver = new DbalMySQLDriver($adapter);
        } else {
            throw new RuntimeException(
                'MySQLSecurityGuard requires PDO or Doctrine\DBAL\Connection. Got: ' . get_debug_type($raw)
            );
        }

        $this->assertSchema($raw);
    }

    // -------------------------------------------------------------------------
    //  Delegation to low-level driver (do* methods)
    // -------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    protected function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        return $this->executeSafely(
            fn() => $this->driver->doRecordFailure($attempt),
            'recordFailure'
        );
    }

    /**
     * @throws Exception
     */
    protected function doResetAttempts(string $ip, string $subject): void
    {
        $this->executeSafely(
            fn() => $this->driver->doResetAttempts($ip, $subject),
            'resetAttempts'
        );
    }

    /**
     * @throws Exception
     */
    protected function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        return $this->executeSafely(
            fn() => $this->driver->doGetActiveBlock($ip, $subject),
            'getActiveBlock'
        );
    }

    /**
     * @throws Exception
     */
    protected function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        return $this->executeSafely(
            fn() => $this->driver->doGetRemainingBlockSeconds($ip, $subject),
            'getRemainingBlockSeconds'
        );
    }

    /**
     * @throws Exception
     */
    protected function doBlock(SecurityBlockDTO $block): void
    {
        $this->executeSafely(
            fn() => $this->driver->doBlock($block),
            'block'
        );
    }

    /**
     * @throws Exception
     */
    protected function doUnblock(string $ip, string $subject): void
    {
        $this->executeSafely(
            fn() => $this->driver->doUnblock($ip, $subject),
            'unblock'
        );
    }

    /**
     * @throws Exception
     */
    protected function doCleanup(): void
    {
        $this->executeSafely(
            fn() => $this->driver->doCleanup(),
            'cleanup'
        );
    }

    /**
     * @return array<string, mixed>
     * @throws Exception
     */
    protected function doGetStats(): array
    {
        return $this->executeSafely(
            fn() => $this->driver->doGetStats(),
            'getStats'
        );
    }

    // -------------------------------------------------------------------------
    //  IntegrationV2 safeguards
    // -------------------------------------------------------------------------

    private function assertConnected(AdapterInterface $adapter): void
    {
        if (! $adapter->isConnected()) {
            throw new RuntimeException('IntegrationV2 MySQL connection failed.');
        }
    }

    private function assertSchema(PDO|Connection $raw): void
    {
        $required = array_map('strtolower', ['sg_attempts', 'sg_blocks']);

        try {
            $missing = [];

            if ($raw instanceof PDO) {
                $placeholders = implode(', ', array_fill(0, count($required), '?'));
                $stmt = $raw->prepare(
                    'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES '
                    . 'WHERE TABLE_SCHEMA = DATABASE() '
                    . 'AND TABLE_NAME IN (' . $placeholders . ')'
                );

                /** @var \PDOStatement|false $stmt */
                if ($stmt instanceof \PDOStatement) {
                    $stmt->execute($required);
                    /** @var array<int,string>|false $present */
                    $present = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
                    if (is_array($present)) {
                        $normalized = array_map('strtolower', $present);
                        $missing = array_values(array_diff($required, $normalized));
                    } else {
                        throw new \RuntimeException('IntegrationV2 MySQL fetch failed.');
                    }
                } else {
                    throw new \RuntimeException('IntegrationV2 MySQL prepare failed.');
                }
            } else {
                /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager<mixed> $schemaManager */
                $schemaManager = $raw->getSchemaManager();
                $tables = $schemaManager->listTableNames();

                $normalized = array_map('strtolower', $tables);
                $missing = array_values(array_diff($required, $normalized));
            }

            if ($missing !== []) {
                throw new RuntimeException('IntegrationV2 MySQL schema missing.');
            }
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException('IntegrationV2 MySQL connection failed.', 0, $e);
        }
    }

    /**
     * @template TReturn
     * @param callable():TReturn $operation
     * @return TReturn
     */
    private function executeSafely(callable $operation, string $context)
    {
        try {
            return $operation();
        } catch (Throwable $e) {
            throw new RuntimeException('IntegrationV2 MySQL connection failed.', 0, $e);
        }
    }
}
