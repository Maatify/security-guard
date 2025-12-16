<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:34
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers\MySQL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Drivers\MySQL\Contracts\MySQLDriverInterface;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use RuntimeException;

final class DbalMySQLDriver implements MySQLDriverInterface
{
    private Connection $db;

    public function __construct(AdapterInterface $adapter)
    {
        $raw = $adapter->getDriver();

        if (! $raw instanceof Connection) {
            throw new RuntimeException(
                'DbalMySQLDriver requires Doctrine\DBAL\Connection. Got: ' . get_debug_type($raw)
            );
        }

        $this->db = $raw;
    }

    // ------------------------------------------------------------------------
    //  doRecordFailure
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        $this->db->insert('sg_attempts', [
            'ip'          => $attempt->ip,
            'subject'     => $attempt->subject,
            'occurred_at' => $attempt->occurredAt,
        ]);

        $sql = <<<SQL
SELECT COUNT(*) AS c
FROM sg_attempts
WHERE ip = :ip
  AND subject = :subject
  AND occurred_at >= (:now - :window)
SQL;

        /** @var array{c:int|string}|false $row */
        $row = $this->db->fetchAssociative($sql, [
            'ip'      => $attempt->ip,
            'subject' => $attempt->subject,
            'now'     => time(),
            'window'  => 3600,
        ]);

        return $row === false ? 0 : (int)$row['c'];
    }

    // ------------------------------------------------------------------------
    //  doResetAttempts
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doResetAttempts(string $ip, string $subject): void
    {
        $this->db->delete('sg_attempts', [
            'ip'      => $ip,
            'subject' => $subject,
        ]);
    }

    // ------------------------------------------------------------------------
    //  doGetActiveBlock
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        $sql = <<<SQL
SELECT ip, subject, type, expires_at, created_at
FROM sg_blocks
WHERE ip = :ip
  AND subject = :subject
  AND (expires_at = 0 OR expires_at > :now)
LIMIT 1
SQL;

        /**
         * @var array{
         *     ip:string,
         *     subject:string,
         *     type:string|int,
         *     expires_at:int|string,
         *     created_at:int|string
         * }|false $row
         */
        $row = $this->db->fetchAssociative($sql, [
            'ip'      => $ip,
            'subject' => $subject,
            'now'     => time(),
        ]);

        if ($row === false) {
            return null;
        }

        $type = BlockTypeEnum::tryFrom((string)$row['type']);
        if ($type === null) {
            return null;
        }

        return new SecurityBlockDTO(
            ip       : (string)$row['ip'],
            subject  : (string)$row['subject'],
            type     : $type,
            expiresAt: (int)$row['expires_at'],
            createdAt: (int)$row['created_at'],
        );
    }

    // ------------------------------------------------------------------------
    //  doGetRemainingBlockSeconds
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        $block = $this->doGetActiveBlock($ip, $subject);

        if ($block === null) {
            return null;
        }

        if ($block->expiresAt === 0) {
            return null;
        }

        return max(0, $block->expiresAt - time());
    }

    // ------------------------------------------------------------------------
    //  doBlock
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doBlock(SecurityBlockDTO $block): void
    {
        $this->db->executeStatement(
            <<<SQL
REPLACE INTO sg_blocks (ip, subject, type, expires_at, created_at)
VALUES (:ip, :subject, :type, :expires, :created)
SQL,
            [
                'ip'      => $block->ip,
                'subject' => $block->subject,
                'type'    => $block->type->value,
                'expires' => $block->expiresAt,
                'created' => $block->createdAt,
            ]
        );
    }

    // ------------------------------------------------------------------------
    //  doUnblock
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doUnblock(string $ip, string $subject): void
    {
        $this->db->delete('sg_blocks', [
            'ip'      => $ip,
            'subject' => $subject,
        ]);
    }

    // ------------------------------------------------------------------------
    //  doCleanup
    // ------------------------------------------------------------------------

    /**
     * @throws Exception
     */
    public function doCleanup(): void
    {
        $now = time();
        $cut = $now - 86400;

        $this->db->executeStatement(
            'DELETE FROM sg_blocks WHERE expires_at != 0 AND expires_at <= :now',
            ['now' => $now]
        );

        $this->db->executeStatement(
            'DELETE FROM sg_attempts WHERE occurred_at < :cut',
            ['cut' => $cut]
        );
    }

    // ------------------------------------------------------------------------
    //  doGetStats
    // ------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     * @throws Exception
     */
    public function doGetStats(): array
    {
        $attemptsRaw = $this->db->fetchOne('SELECT COUNT(*) FROM sg_attempts');
        $blocksRaw = $this->db->fetchOne('SELECT COUNT(*) FROM sg_blocks');

        $attempts = is_numeric($attemptsRaw) ? (int)$attemptsRaw : 0;
        $blocks = is_numeric($blocksRaw) ? (int)$blocksRaw : 0;

        return [
            'attempts' => $attempts,
            'blocks'   => $blocks,
        ];
    }
}
