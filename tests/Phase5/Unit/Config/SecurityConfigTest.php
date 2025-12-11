<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Config;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use PHPUnit\Framework\TestCase;

class SecurityConfigTest extends TestCase
{
    public function testDefaults(): void
    {
        $dto = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'sec_guard',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );

        $config = new SecurityConfig($dto);

        $this->assertSame(5, $config->maxFailures());
        $this->assertSame(300, $config->blockSeconds());
        $this->assertSame(60, $config->windowSeconds());
        $this->assertFalse($config->backoffEnabled());
    }
}
