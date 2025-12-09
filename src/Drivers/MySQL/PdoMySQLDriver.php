<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:17
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers\MySQL;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Drivers\MySQL\Contracts\MySQLDriverInterface;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PDO;
use RuntimeException;

final class PdoMySQLDriver implements MySQLDriverInterface
{
    private PDO $pdo;

    public function __construct(AdapterInterface $adapter)
    {
        $raw = $adapter->getDriver();

        if (! $raw instanceof PDO) {
            throw new RuntimeException(
                'PdoMySQLDriver requires a PDO driver. Got: ' . get_debug_type($raw)
            );
        }

        $this->pdo = $raw;
    }

    // ------------------------------------------------------------------------
    //  doRecordFailure
    // ------------------------------------------------------------------------

    public function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sg_attempts (ip, subject, occurred_at)
             VALUES (:ip, :subject, :ts)'
        );

        $stmt->execute([
            ':ip'      => $attempt->ip,
            ':subject' => $attempt->subject,
            ':ts'      => $attempt->occurredAt,
        ]);

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS c
               FROM sg_attempts
              WHERE ip = :ip
                AND subject = :subject
                AND occurred_at >= (:now - :window)'
        );

        $stmt->execute([
            ':ip'      => $attempt->ip,
            ':subject' => $attempt->subject,
            ':now'     => time(),
            ':window'  => 3600,
        ]);

        /** @var array{c:string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($row['c']) ? (int)$row['c'] : 0;
    }

    // ------------------------------------------------------------------------
    //  doResetAttempts
    // ------------------------------------------------------------------------

    public function doResetAttempts(string $ip, string $subject): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM sg_attempts WHERE ip = :ip AND subject = :subject'
        );

        $stmt->execute([
            ':ip'      => $ip,
            ':subject' => $subject,
        ]);
    }

    // ------------------------------------------------------------------------
    //  doGetActiveBlock
    // ------------------------------------------------------------------------

    public function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        $stmt = $this->pdo->prepare(
            'SELECT type, expires_at, created_at
               FROM sg_blocks
              WHERE ip = :ip
                AND subject = :subject
                AND (expires_at = 0 OR expires_at > :now)
              LIMIT 1'
        );

        $stmt->execute([
            ':ip'      => $ip,
            ':subject' => $subject,
            ':now'     => time(),
        ]);

        /** @var array{type:string,expires_at:int|string,created_at:int|string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            return null;
        }

        return new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: BlockTypeEnum::from($row['type']),
            expiresAt: (int)$row['expires_at'],
            createdAt: (int)$row['created_at'],
        );
    }

    // ------------------------------------------------------------------------
    //  doGetRemainingBlockSeconds
    // ------------------------------------------------------------------------

    public function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT expires_at
               FROM sg_blocks
              WHERE ip = :ip
                AND subject = :subject
                AND (expires_at = 0 OR expires_at > :now)
              LIMIT 1'
        );

        $stmt->execute([
            ':ip'      => $ip,
            ':subject' => $subject,
            ':now'     => time(),
        ]);

        /** @var array{expires_at:int|string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            return null;
        }

        $expiresAt = (int)$row['expires_at'];

        if ($expiresAt === 0) {
            return null;
        }

        return max(0, $expiresAt - time());
    }

    // ------------------------------------------------------------------------
    //  doBlock
    // ------------------------------------------------------------------------

    public function doBlock(SecurityBlockDTO $block): void
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sg_blocks (ip, subject, type, expires_at, created_at)
             VALUES (:ip, :subject, :type, :expires, :created)'
        );

        $stmt->execute([
            ':ip'      => $block->ip,
            ':subject' => $block->subject,
            ':type'    => $block->type->value,
            ':expires' => $block->expiresAt,
            ':created' => $block->createdAt,
        ]);
    }

    // ------------------------------------------------------------------------
    //  doUnblock
    // ------------------------------------------------------------------------

    public function doUnblock(string $ip, string $subject): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM sg_blocks WHERE ip = :ip AND subject = :subject'
        );

        $stmt->execute([
            ':ip'      => $ip,
            ':subject' => $subject,
        ]);
    }

    // ------------------------------------------------------------------------
    //  doCleanup
    // ------------------------------------------------------------------------

    public function doCleanup(): void
    {
        $now = time();

        // حذف البلوكات المنتهية
        $stmt = $this->pdo->prepare(
            'DELETE FROM sg_blocks
              WHERE expires_at != 0
                AND expires_at <= :now'
        );
        $stmt->execute([':now' => $now]);

        // حذف محاولات قديمة
        $cut = $now - 86400;

        $stmt = $this->pdo->prepare(
            'DELETE FROM sg_attempts
              WHERE occurred_at < :cut'
        );
        $stmt->execute([':cut' => $cut]);
    }

    // ------------------------------------------------------------------------
    //  doGetStats
    // ------------------------------------------------------------------------

    public function doGetStats(): array
    {
        $stmt1 = $this->pdo->query('SELECT COUNT(*) FROM sg_attempts');
        $stmt2 = $this->pdo->query('SELECT COUNT(*) FROM sg_blocks');

        $attempts = ($stmt1 !== false)
            ? (int)($stmt1->fetchColumn() ?: 0)
            : 0;

        $blocks = ($stmt2 !== false)
            ? (int)($stmt2->fetchColumn() ?: 0)
            : 0;

        return [
            'attempts' => $attempts,
            'blocks'   => $blocks,
        ];
    }
}
