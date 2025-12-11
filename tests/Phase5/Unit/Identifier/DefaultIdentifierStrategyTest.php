<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Identifier;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class DefaultIdentifierStrategyTest extends TestCase
{
    public function testMakeId(): void
    {
        $dto = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: 'test_pfx',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $config = new SecurityConfig($dto);

        $strategy = new DefaultIdentifierStrategy($config);
        $id = $strategy->makeId('192.168.1.1', 'userA');

        $this->assertNotEmpty($id);

        // Consistency check
        $id2 = $strategy->makeId('192.168.1.1', 'userA');
        $this->assertSame($id, $id2);

        // Diff check
        $id3 = $strategy->makeId('192.168.1.1', 'userB');
        $this->assertNotSame($id, $id3);
    }
}
