<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Config;

use Maatify\SecurityGuard\Config\SecurityConfig;
use PHPUnit\Framework\TestCase;

class SecurityConfigTest extends TestCase
{
    public function testDefaults(): void
    {
        $config = new SecurityConfig(maxFailures: 5, blockSeconds: 300);

        $this->assertSame(5, $config->maxFailures());
        $this->assertSame(300, $config->blockSeconds());
    }
}
