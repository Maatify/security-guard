<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 01:23
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Config;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use InvalidArgumentException;

/**
 * SecurityConfig centralizes validation and normalized access
 * to all configuration values used by drivers and services.
 */
final class SecurityConfig
{
    private int $windowSeconds;
    private int $blockSeconds;
    private int $maxFailures;

    private IdentifierModeEnum $identifierMode;
    private string $keyPrefix;

    private bool $backoffEnabled;
    private int $initialBackoffSeconds;
    private float $backoffMultiplier;
    private int $maxBackoffSeconds;

    public function __construct(SecurityConfigDTO $dto)
    {
        $this->validate($dto);

        $this->windowSeconds = $dto->windowSeconds;
        $this->blockSeconds = $dto->blockSeconds;
        $this->maxFailures = $dto->maxFailures;

        $this->identifierMode = $dto->identifierMode;
        $this->keyPrefix = rtrim($dto->keyPrefix, ':') . ':';

        $this->backoffEnabled = $dto->backoffEnabled;
        $this->initialBackoffSeconds = $dto->initialBackoffSeconds;
        $this->backoffMultiplier = $dto->backoffMultiplier;
        $this->maxBackoffSeconds = $dto->maxBackoffSeconds;
    }

    /**
     * Validates raw DTO values.
     *
     * @throws InvalidArgumentException
     */
    private function validate(SecurityConfigDTO $dto): void
    {
        if ($dto->windowSeconds < 1) {
            throw new InvalidArgumentException('windowSeconds must be >= 1');
        }

        if ($dto->blockSeconds < 1) {
            throw new InvalidArgumentException('blockSeconds must be >= 1');
        }

        if ($dto->maxFailures < 1) {
            throw new InvalidArgumentException('maxFailures must be >= 1');
        }

        if ($dto->backoffEnabled) {
            if ($dto->initialBackoffSeconds < 1) {
                throw new InvalidArgumentException('initialBackoffSeconds must be >= 1');
            }

            if ($dto->backoffMultiplier < 1.0) {
                throw new InvalidArgumentException('backoffMultiplier must be >= 1.0');
            }

            if ($dto->maxBackoffSeconds < $dto->initialBackoffSeconds) {
                throw new InvalidArgumentException('maxBackoffSeconds must be >= initialBackoffSeconds');
            }
        }
    }

    // ===========================
    //        Public API
    // ===========================

    public function windowSeconds(): int
    {
        return $this->windowSeconds;
    }

    public function blockSeconds(): int
    {
        return $this->blockSeconds;
    }

    public function maxFailures(): int
    {
        return $this->maxFailures;
    }

    public function identifierMode(): IdentifierModeEnum
    {
        return $this->identifierMode;
    }

    public function keyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function backoffEnabled(): bool
    {
        return $this->backoffEnabled;
    }

    /**
     * Calculates backoff block duration based on failure count.
     */
    public function computeBackoffSeconds(int $failureCount): int
    {
        if (! $this->backoffEnabled || $failureCount < $this->maxFailures) {
            return $this->blockSeconds;
        }

        $seconds = (int)($this->initialBackoffSeconds * ($this->backoffMultiplier ** ($failureCount - $this->maxFailures)));

        return min($seconds, $this->maxBackoffSeconds);
    }
}
