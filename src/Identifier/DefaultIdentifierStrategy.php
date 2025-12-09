<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:58
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Identifier;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;

final class DefaultIdentifierStrategy implements IdentifierStrategyInterface
{
    private SecurityConfig $config;

    public function __construct(SecurityConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param   array<string,mixed>  $context
     *
     * @throws \JsonException
     */
    public function makeId(string $ip, string $subject, array $context = []): string
    {
        $mode = $this->config->identifierMode();

        $base = match ($mode) {
            IdentifierModeEnum::IDENTIFIER_ONLY => $subject,
            IdentifierModeEnum::IP_ONLY => $ip,
            IdentifierModeEnum::IDENTIFIER_AND_IP => $subject . '|' . $ip,
        };

        if (! empty($context)) {
            /** @var array<string,mixed> $context */
            $ctx = json_encode($context, JSON_THROW_ON_ERROR);
            $base .= '|' . $ctx;
        }

        return hash('sha256', $this->config->keyPrefix() . $base);
    }
}
