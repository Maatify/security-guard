<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase5\Unit\Identifier;

use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class DefaultIdentifierStrategyTest extends TestCase
{
    public function testMakeId(): void
    {
        $strategy = new DefaultIdentifierStrategy();
        $id = $strategy->makeId('192.168.1.1', 'userA');

        // Strategy implementation might vary (md5/sha256 or simple concat)
        // From api map, it takes ip and subject.
        // Assuming implementation is consistent, we assert it returns a non-empty string.
        $this->assertNotEmpty($id);
        $this->assertIsString($id);

        // Consistency check
        $id2 = $strategy->makeId('192.168.1.1', 'userA');
        $this->assertSame($id, $id2);

        // Diff check
        $id3 = $strategy->makeId('192.168.1.1', 'userB');
        $this->assertNotSame($id, $id3);
    }
}
