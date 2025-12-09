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

/**
 * Immutable configuration DTO for Security Guard.
 * Holds raw configuration values without logic.
 */
final readonly class SecurityConfigDTO
{
    public function __construct(
        public int $windowSeconds,
        public int $blockSeconds,
        public int $maxFailures,
        public IdentifierModeEnum $identifierMode,
        public string $keyPrefix,

        // Backoff configuration
        public bool $backoffEnabled,
        public int $initialBackoffSeconds,
        public float $backoffMultiplier,
        public int $maxBackoffSeconds,
    )
    {
    }
}
