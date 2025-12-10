<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Resolver;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Resolver\SecurityGuardResolver;
use PHPUnit\Framework\TestCase;

class SecurityGuardResolverTest extends TestCase
{
    private function createConfig(): SecurityConfig
    {
        return new SecurityConfig(new SecurityConfigDTO(
            60, 300, 5, IdentifierModeEnum::IP_ONLY, 'prefix', true, 10, 2.0, 100
        ));
    }

    public function testResolveKey(): void
    {
        $config = $this->createConfig();
        $strategy = new DefaultIdentifierStrategy($config);
        $resolver = new SecurityGuardResolver($config, $strategy);

        // prefix:IP (from strategy)
        // prefix:127.0.0.1
        $this->assertSame('prefix:127.0.0.1', $resolver->resolveKey('127.0.0.1', 'user'));
    }

    public function testResolveBlockKey(): void
    {
        $config = $this->createConfig();
        $strategy = new DefaultIdentifierStrategy($config);
        $resolver = new SecurityGuardResolver($config, $strategy);

        // prefix:block:IP
        $this->assertSame('prefix:block:127.0.0.1', $resolver->resolveBlockKey('127.0.0.1', 'user'));
    }
}
