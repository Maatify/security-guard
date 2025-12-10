<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Identifier;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class DefaultIdentifierStrategyTest extends TestCase
{
    private function createConfig(IdentifierModeEnum $mode): SecurityConfig
    {
        return new SecurityConfig(new SecurityConfigDTO(
            60, 300, 5, $mode, 'sg', true, 10, 2.0, 100
        ));
    }

    public function testIdentifierOnly(): void
    {
        $config = $this->createConfig(IdentifierModeEnum::IDENTIFIER_ONLY);
        $strategy = new DefaultIdentifierStrategy($config);

        $this->assertSame('test_user', $strategy->getIdentifier('127.0.0.1', 'test_user'));
    }

    public function testIpOnly(): void
    {
        $config = $this->createConfig(IdentifierModeEnum::IP_ONLY);
        $strategy = new DefaultIdentifierStrategy($config);

        $this->assertSame('127.0.0.1', $strategy->getIdentifier('127.0.0.1', 'test_user'));
    }

    public function testIdentifierAndIp(): void
    {
        $config = $this->createConfig(IdentifierModeEnum::IDENTIFIER_AND_IP);
        $strategy = new DefaultIdentifierStrategy($config);

        $this->assertSame('test_user:127.0.0.1', $strategy->getIdentifier('127.0.0.1', 'test_user'));
    }
}
