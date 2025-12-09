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
    }

    // -------------------------------------------------------------------------
    //  Delegation to low-level driver (do* methods)
    // -------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    protected function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        return $this->driver->doRecordFailure($attempt);
    }

    /**
     * @throws Exception
     */
    protected function doResetAttempts(string $ip, string $subject): void
    {
        $this->driver->doResetAttempts($ip, $subject);
    }

    /**
     * @throws Exception
     */
    protected function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        return $this->driver->doGetActiveBlock($ip, $subject);
    }

    /**
     * @throws Exception
     */
    protected function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        return $this->driver->doGetRemainingBlockSeconds($ip, $subject);
    }

    /**
     * @throws Exception
     */
    protected function doBlock(SecurityBlockDTO $block): void
    {
        $this->driver->doBlock($block);
    }

    /**
     * @throws Exception
     */
    protected function doUnblock(string $ip, string $subject): void
    {
        $this->driver->doUnblock($ip, $subject);
    }

    /**
     * @throws Exception
     */
    protected function doCleanup(): void
    {
        $this->driver->doCleanup();
    }

    /**
     * @return array<string, mixed>
     * @throws Exception
     */
    protected function doGetStats(): array
    {
        return $this->driver->doGetStats();
    }
}
