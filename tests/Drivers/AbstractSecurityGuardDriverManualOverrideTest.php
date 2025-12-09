<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 10:16
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Drivers;

use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use PHPUnit\Framework\TestCase;

final class AbstractSecurityGuardDriverManualOverrideTest extends TestCase
{
    private FakeSecurityGuardDriver $driver;

    protected function setUp(): void
    {
        $adapter = new \Maatify\SecurityGuard\Tests\Fake\FakeKeyValueAdapter();

        $this->driver = new FakeSecurityGuardDriver(
            $adapter,
            new FakeIdentifierStrategy()
        );
    }

    public function testManualBlockOverridesAutoBlock(): void
    {
        $ip = '10.0.0.1';
        $subject = 'user';

        // ✅ Auto block (short)
        $autoBlock = new SecurityBlockDTO(
            ip       : $ip,
            subject  : $subject,
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 3,   // 3 seconds
            createdAt: time()
        );

        $this->driver->block($autoBlock);

        $this->assertTrue($this->driver->isBlocked($ip, $subject));

        // ✅ Manual block (longer)
        $manualBlock = new SecurityBlockDTO(
            ip       : $ip,
            subject  : $subject,
            type     : BlockTypeEnum::MANUAL,
            expiresAt: time() + 10,  // 10 seconds
            createdAt: time()
        );

        $this->driver->block($manualBlock);

        // ✅ Still blocked
        $this->assertTrue($this->driver->isBlocked($ip, $subject));

        // ✅ Remaining time must match MANUAL not AUTO
        $remain = $this->driver->getRemainingBlockSeconds($ip, $subject);

        $this->assertNotNull($remain);
        $this->assertTrue($remain > 5, 'Manual block did not override auto block TTL');
    }

    public function testAutoBlockMustNotOverrideManualBlock(): void
    {
        $ip = '10.0.0.2';
        $subject = 'user';

        // ✅ Manual block first
        $manualBlock = new SecurityBlockDTO(
            ip       : $ip,
            subject  : $subject,
            type     : BlockTypeEnum::MANUAL,
            expiresAt: time() + 10,
            createdAt: time()
        );

        $this->driver->block($manualBlock);

        // ✅ Try to override with weaker AUTO block
        $autoBlock = new SecurityBlockDTO(
            ip       : $ip,
            subject  : $subject,
            type     : BlockTypeEnum::AUTO,
            expiresAt: time() + 3,
            createdAt: time()
        );

        $this->driver->block($autoBlock);

        // ✅ Still blocked by MANUAL not AUTO
        $remain = $this->driver->getRemainingBlockSeconds($ip, $subject);

        $this->assertNotNull($remain);
        $this->assertTrue($remain > 5, 'Auto block incorrectly downgraded manual block');
    }
}
