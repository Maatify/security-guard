<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:59
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Identifier;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class DefaultIdentifierStrategyTest extends TestCase
{
    private function config(IdentifierModeEnum $mode): SecurityConfig
    {
        $dto = new SecurityConfigDTO(
            windowSeconds        : 60,
            blockSeconds         : 60,
            maxFailures          : 5,
            identifierMode       : $mode,
            keyPrefix            : 'sg',
            backoffEnabled       : false,
            initialBackoffSeconds: 10,
            backoffMultiplier    : 2.0,
            maxBackoffSeconds    : 100,
        );

        return new SecurityConfig($dto);
    }

    public function testIdentifierOnly(): void
    {
        $strategy = new DefaultIdentifierStrategy($this->config(IdentifierModeEnum::IDENTIFIER_ONLY));

        $id1 = $strategy->makeId('1.1.1.1', 'user123');
        $id2 = $strategy->makeId('5.5.5.5', 'user123');

        $this->assertSame($id1, $id2); // IP ignored
    }

    public function testIpOnly(): void
    {
        $strategy = new DefaultIdentifierStrategy($this->config(IdentifierModeEnum::IP_ONLY));

        $id1 = $strategy->makeId('1.1.1.1', 'subjectA');
        $id2 = $strategy->makeId('1.1.1.1', 'subjectB');

        $this->assertSame($id1, $id2); // subject ignored
    }

    public function testIdentifierAndIp(): void
    {
        $strategy = new DefaultIdentifierStrategy($this->config(IdentifierModeEnum::IDENTIFIER_AND_IP));

        $idA = $strategy->makeId('1.1.1.1', 'userA');
        $idB = $strategy->makeId('1.1.1.1', 'userB');

        $this->assertNotSame($idA, $idB);
    }

    public function testContextChangesHash(): void
    {
        $strategy = new DefaultIdentifierStrategy($this->config(IdentifierModeEnum::IDENTIFIER_ONLY));

        $id1 = $strategy->makeId('1.1.1.1', 'user123', ['device' => 'A']);
        $id2 = $strategy->makeId('1.1.1.1', 'user123', ['device' => 'B']);

        $this->assertNotSame($id1, $id2);
    }

    public function testDeterministicHash(): void
    {
        $strategy = new DefaultIdentifierStrategy($this->config(IdentifierModeEnum::IDENTIFIER_AND_IP));

        $id1 = $strategy->makeId('1.1.1.1', 'user123', ['x' => 1]);
        $id2 = $strategy->makeId('1.1.1.1', 'user123', ['x' => 1]);

        $this->assertSame($id1, $id2);
    }
}
