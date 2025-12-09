<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:48
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Config;

use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use PHPUnit\Framework\TestCase;

class SecurityConfigDTOTest extends TestCase
{
    public function testConstructorStoresValuesCorrectly(): void
    {
        $dto = new SecurityConfigDTO(
            windowSeconds        : 60,
            blockSeconds         : 300,
            maxFailures          : 5,
            identifierMode       : IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix            : 'sec:',
            backoffEnabled       : true,
            initialBackoffSeconds: 10,
            backoffMultiplier    : 2.5,
            maxBackoffSeconds    : 300
        );

        $this->assertSame(60, $dto->windowSeconds);
        $this->assertSame(300, $dto->blockSeconds);
        $this->assertSame(5, $dto->maxFailures);

        $this->assertSame(IdentifierModeEnum::IDENTIFIER_AND_IP, $dto->identifierMode);

        $this->assertSame('sec:', $dto->keyPrefix);

        $this->assertTrue($dto->backoffEnabled);
        $this->assertSame(10, $dto->initialBackoffSeconds);
        $this->assertSame(2.5, $dto->backoffMultiplier);
        $this->assertSame(300, $dto->maxBackoffSeconds);
    }
}
