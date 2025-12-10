<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use PHPUnit\Framework\TestCase;

class SecurityGuardLogicTest extends TestCase
{
    private function createDriver(IdentifierModeEnum $mode): FakeSecurityGuardDriver
    {
        $configDTO = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: $mode,
            keyPrefix: 'test_logic:',
            backoffEnabled: false,
            initialBackoffSeconds: 0,
            backoffMultiplier: 1.0,
            maxBackoffSeconds: 0
        );
        $config = new SecurityConfig($configDTO);
        $strategy = new DefaultIdentifierStrategy($config);
        $adapter = new FakeAdapter();

        return new FakeSecurityGuardDriver($adapter, $strategy);
    }

    // Phase 5: Multiple IPs Same Subject
    public function testMultipleIPsSameSubjectIsolation(): void
    {
        // Use IDENTIFIER_AND_IP mode so IP matters
        $driver = $this->createDriver(IdentifierModeEnum::IDENTIFIER_AND_IP);

        $attempt1 = new LoginAttemptDTO('1.1.1.1', 'user1', time(), 60, null, []);
        $attempt2 = new LoginAttemptDTO('2.2.2.2', 'user1', time(), 60, null, []);

        $driver->recordFailure($attempt1);

        // Should be independent
        // This depends on the Strategy hashing IP + Subject
        // Check if stats or failure counts are independent?
        // recordFailure returns the new count.

        $count2 = $driver->recordFailure($attempt2);

        $this->assertSame(1, $count2, 'Different IP should start new failure count for same subject in IP+Subject mode');

        // If we block one IP for user1
        $block = new SecurityBlockDTO('1.1.1.1', 'user1', BlockTypeEnum::AUTO, time() + 10, time());
        $driver->block($block);

        $this->assertTrue($driver->isBlocked('1.1.1.1', 'user1'));
        $this->assertFalse($driver->isBlocked('2.2.2.2', 'user1'), 'Block on IP 1 should not affect IP 2');
    }

    // Phase 6: Same IP Multiple Subjects
    public function testSameIPMultipleSubjectsIsolation(): void
    {
        // Use IDENTIFIER_AND_IP mode
        $driver = $this->createDriver(IdentifierModeEnum::IDENTIFIER_AND_IP);

        $attempt1 = new LoginAttemptDTO('1.1.1.1', 'userA', time(), 60, null, []);
        $attempt2 = new LoginAttemptDTO('1.1.1.1', 'userB', time(), 60, null, []);

        $driver->recordFailure($attempt1);
        $count2 = $driver->recordFailure($attempt2);

        $this->assertSame(1, $count2, 'Different Subject should start new failure count for same IP');

        $block = new SecurityBlockDTO('1.1.1.1', 'userA', BlockTypeEnum::AUTO, time() + 10, time());
        $driver->block($block);

        $this->assertTrue($driver->isBlocked('1.1.1.1', 'userA'));
        $this->assertFalse($driver->isBlocked('1.1.1.1', 'userB'));
    }

    // Phase 7: Identifier Collision Handling
    public function testIdentifierCollisionBehavior(): void
    {
        // If mode is IDENTIFIER_ONLY, different IPs for same subject COLLIDE (are same ID)
        $driver = $this->createDriver(IdentifierModeEnum::IDENTIFIER_ONLY);

        $attempt1 = new LoginAttemptDTO('1.1.1.1', 'userX', time(), 60, null, []);
        $attempt2 = new LoginAttemptDTO('2.2.2.2', 'userX', time(), 60, null, []);

        $driver->recordFailure($attempt1);
        $count2 = $driver->recordFailure($attempt2);

        $this->assertSame(2, $count2, 'IDENTIFIER_ONLY mode should treat different IPs as same failure counter for subject');

        // If mode is IP_ONLY
        $driver2 = $this->createDriver(IdentifierModeEnum::IP_ONLY);
        $attempt3 = new LoginAttemptDTO('3.3.3.3', 'userY', time(), 60, null, []);
        $attempt4 = new LoginAttemptDTO('3.3.3.3', 'userZ', time(), 60, null, []);

        $driver2->recordFailure($attempt3);
        $count4 = $driver2->recordFailure($attempt4);

        $this->assertSame(2, $count4, 'IP_ONLY mode should treat different Subjects as same failure counter for IP');
    }
}
