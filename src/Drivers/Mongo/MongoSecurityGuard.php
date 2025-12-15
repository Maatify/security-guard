<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 02:45
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Drivers\Mongo;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;
use MongoDB\Collection;
use MongoDB\Database;
use RuntimeException;

final class MongoSecurityGuard extends AbstractSecurityGuardDriver
{
    private Database $db;

    private string $attempts = 'sg_attempts';
    private string $blocks   = 'sg_blocks';

    public function __construct(
        AdapterInterface $adapter,
        IdentifierStrategyInterface $strategy
    ) {
        parent::__construct($adapter, $strategy);

        $driver = $adapter->getDriver();

        if (!$driver instanceof Database) {
            throw new RuntimeException(
                'MongoSecurityGuard requires MongoDB\Database driver. Got: ' . get_debug_type($driver)
            );
        }

        $this->db = $driver;

        $this->ensureIndexes();
    }

    // -------------------------------------------------------------------------
    //  Typed Collection Resolver
    // -------------------------------------------------------------------------

    /**
     * @param   string  $collection
     *
     * @return Collection
     */
    private function col(string $collection): Collection
    {
        /** @var Collection $col */
        $col = $this->db->selectCollection($collection);

        return $col;
    }

    // -------------------------------------------------------------------------
    //  Ensure Indexes
    // -------------------------------------------------------------------------

    private function ensureIndexes(): void
    {
        // TTL for attempts (1 day)
        $this->col($this->attempts)->createIndex(
            ['occurred_at' => 1],
            ['expireAfterSeconds' => 86400]
        );

        // TTL for blocks - expires_at > 0
        $this->col($this->blocks)->createIndex(
            ['expires_at' => 1],
            ['expireAfterSeconds' => 0]
        );

        $this->col($this->attempts)->createIndex(['ip' => 1, 'subject' => 1]);
        $this->col($this->blocks)->createIndex(['ip' => 1, 'subject' => 1]);
    }

    // -------------------------------------------------------------------------
    //  doRecordFailure()
    // -------------------------------------------------------------------------

    protected function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        $this->col($this->attempts)->insertOne([
            'ip'          => $attempt->ip,
            'subject'     => $attempt->subject,
            'occurred_at' => $attempt->occurredAt,
        ]);

        $window = $this->now() - 3600;

        return $this->col($this->attempts)->countDocuments([
            'ip'          => $attempt->ip,
            'subject'     => $attempt->subject,
            'occurred_at' => ['$gte' => $window],
        ]);
    }

    // -------------------------------------------------------------------------
    //  doResetAttempts()
    // -------------------------------------------------------------------------

    protected function doResetAttempts(string $ip, string $subject): void
    {
        $this->col($this->attempts)->deleteMany([
            'ip'      => $ip,
            'subject' => $subject,
        ]);
    }

    // -------------------------------------------------------------------------
    //  doGetActiveBlock()
    // -------------------------------------------------------------------------

    protected function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        $now = $this->now();

        /** @var array{
         *     type:string,
         *     expires_at:int,
         *     created_at:int
         * }|null $row
         */
        $row = $this->col($this->blocks)->findOne([
            'ip'      => $ip,
            'subject' => $subject,
            '$or'     => [
                ['expires_at' => 0],
                ['expires_at' => ['$gt' => $now]],
            ],
        ]);

        if ($row === null) {
            return null;
        }

        $type = BlockTypeEnum::tryFrom($row['type']);
        if ($type === null) {
            return null;
        }

        return new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: $type,
            expiresAt: (int)$row['expires_at'],
            createdAt: (int)$row['created_at'],
        );
    }

    // -------------------------------------------------------------------------
    //  doGetRemainingBlockSeconds()
    // -------------------------------------------------------------------------

    protected function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        $block = $this->doGetActiveBlock($ip, $subject);

        if ($block === null) {
            return null;
        }

        if ($block->expiresAt === 0) {
            return null; // Permanent block
        }

        return max(0, $block->expiresAt - $this->now());
    }

    // -------------------------------------------------------------------------
    //  doBlock()
    // -------------------------------------------------------------------------

    protected function doBlock(SecurityBlockDTO $block): void
    {
        $this->col($this->blocks)->updateOne(
            ['ip' => $block->ip, 'subject' => $block->subject],
            [
                '$set' => [
                    'type'       => $block->type->value,
                    'expires_at' => $block->expiresAt,
                    'created_at' => $block->createdAt,
                ],
            ],
            ['upsert' => true]
        );
    }

    // -------------------------------------------------------------------------
    //  doUnblock()
    // -------------------------------------------------------------------------

    protected function doUnblock(string $ip, string $subject): void
    {
        $this->col($this->blocks)->deleteOne([
            'ip'      => $ip,
            'subject' => $subject,
        ]);
    }

    // -------------------------------------------------------------------------
    //  doCleanup()
    // -------------------------------------------------------------------------

    protected function doCleanup(): void
    {
        $now = $this->now();

        // Remove expired blocks (except permanent)
        $this->col($this->blocks)->deleteMany([
            'expires_at' => ['$ne' => 0, '$lte' => $now],
        ]);

        // Attempts cleaned automatically via TTL index
    }

    // -------------------------------------------------------------------------
    //  doGetStats()
    // -------------------------------------------------------------------------

    protected function doGetStats(): array
    {
        $attempts = $this->col($this->attempts)->countDocuments();
        $blocks   = $this->col($this->blocks)->countDocuments();

        return [
            'attempts' => $attempts,
            'blocks'   => $blocks,
        ];
    }
}
