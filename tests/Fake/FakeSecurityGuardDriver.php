<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 04:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Fake;

use Maatify\Common\Contracts\Adapter\KeyValueAdapterInterface;
use Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

class FakeSecurityGuardDriver extends AbstractSecurityGuardDriver
{
    protected function failKey(string $ip, string $subject): string
    {
        return 'fail:' . $this->makeIdentifier($ip, $subject);
    }

    protected function blockKey(string $ip, string $subject): string
    {
        return 'block:' . $this->makeIdentifier($ip, $subject);
    }

    protected function doRecordFailure(LoginAttemptDTO $attempt): int
    {
        $key = $this->failKey($attempt->ip, $attempt->subject);
        $raw = $this->kv()->get($key);

        $count = is_numeric($raw) ? (int) $raw : 0;
        $count++;

        $ttl = $attempt->resetAfter > 0 ? $attempt->resetAfter : null;

        $this->kv()->set($key, $count, $ttl);

        return $count;
    }

    protected function doResetAttempts(string $ip, string $subject): void
    {
        $this->kv()->del($this->failKey($ip, $subject));
    }

    protected function doGetActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        $raw = $this->kv()->get($this->blockKey($ip, $subject));
        if (!is_string($raw)) {
            return null;
        }

        /** @var array<string,mixed>|null $data */
        $data = json_decode($raw, true);

        return is_array($data) ? $this->decodeBlock($data) : null;
    }

    protected function doGetRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        $block = $this->doGetActiveBlock($ip, $subject);
        if (! $block) {
            return null;
        }

        $remain = $block->expiresAt - time();
        return $remain > 0 ? $remain : null;
    }

    protected function doBlock(SecurityBlockDTO $block): void
    {
        // ✅ Prevent AUTO from overriding MANUAL
        $existing = $this->doGetActiveBlock($block->ip, $block->subject);

        if (
            $existing !== null &&
            $existing->type === BlockTypeEnum::MANUAL &&
            $block->type === BlockTypeEnum::AUTO
        ) {
            // ❌ Auto block must NOT override manual block
            return;
        }

        $ttl = $block->expiresAt - time();
        $raw = json_encode($this->encodeBlock($block));

        $this->kv()->set(
            $this->blockKey($block->ip, $block->subject),
            $raw,
            $ttl > 0 ? $ttl : null
        );
    }


    protected function doUnblock(string $ip, string $subject): void
    {
        $this->kv()->del($this->blockKey($ip, $subject));
    }

    protected function doCleanup(): void
    {
        // Redis fake adapter handles expirations automatically
    }

    protected function doGetStats(): array
    {
        return [
            'failures' => true,
            'blocks'   => true,
        ];
    }

    private function kv(): KeyValueAdapterInterface
    {
        if (! $this->adapter instanceof KeyValueAdapterInterface) {
            throw new \LogicException('FakeSecurityGuardDriver requires KeyValueAdapterInterface');
        }

        return $this->adapter;
    }
}


