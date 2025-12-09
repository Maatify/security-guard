<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:46
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Config\Enum;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use PHPUnit\Framework\TestCase;

class IdentifierModeEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('identifier_only', IdentifierModeEnum::IDENTIFIER_ONLY->value);
        $this->assertSame('identifier_and_ip', IdentifierModeEnum::IDENTIFIER_AND_IP->value);
        $this->assertSame('ip_only', IdentifierModeEnum::IP_ONLY->value);
    }

    public function testEnumFrom(): void
    {
        $this->assertSame(
            IdentifierModeEnum::IDENTIFIER_ONLY,
            IdentifierModeEnum::from('identifier_only')
        );

        $this->assertSame(
            IdentifierModeEnum::IDENTIFIER_AND_IP,
            IdentifierModeEnum::from('identifier_and_ip')
        );

        $this->assertSame(
            IdentifierModeEnum::IP_ONLY,
            IdentifierModeEnum::from('ip_only')
        );
    }

    public function testEnumCasesCount(): void
    {
        $cases = IdentifierModeEnum::cases();

        $this->assertCount(3, $cases);

        $this->assertContains(IdentifierModeEnum::IDENTIFIER_ONLY, $cases);
        $this->assertContains(IdentifierModeEnum::IDENTIFIER_AND_IP, $cases);
        $this->assertContains(IdentifierModeEnum::IP_ONLY, $cases);
    }
}
