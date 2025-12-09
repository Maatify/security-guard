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

use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;

class FakeIdentifierStrategy implements IdentifierStrategyInterface
{
    public function makeId(string $ip, string $subject, array $context = []): string
    {
        return $ip . ':' . $subject;
    }
}
