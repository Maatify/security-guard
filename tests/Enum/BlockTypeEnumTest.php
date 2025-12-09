<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 17:21
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Enum;

use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PHPUnit\Framework\TestCase;

final class BlockTypeEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('auto', BlockTypeEnum::AUTO->value);
        $this->assertSame('manual', BlockTypeEnum::MANUAL->value);
        $this->assertSame('system', BlockTypeEnum::SYSTEM->value);
    }

    public function testEnumFromString(): void
    {
        $this->assertSame(BlockTypeEnum::AUTO, BlockTypeEnum::from('auto'));
        $this->assertSame(BlockTypeEnum::MANUAL, BlockTypeEnum::from('manual'));
        $this->assertSame(BlockTypeEnum::SYSTEM, BlockTypeEnum::from('system'));
    }
}
