<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-02-24 10:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\IntegrationV2\MySQL;

use Maatify\Bootstrap\Core\EnvironmentLoader;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\SecurityGuard\Drivers\MySQLSecurityGuard;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Tests\IntegrationV2\BaseIntegrationV2TestCase;

/**
 * MySQLPersistenceTest
 *
 * Verifies that MySQL state persists across different guard instances, ensuring real persistence.
 */
class MySQLPersistenceTest extends BaseIntegrationV2TestCase
{
    private ?MySQLSecurityGuard $guard1 = null;
    private ?MySQLSecurityGuard $guard2 = null;

    protected function validateEnvironment(): void
    {
    }

    protected function createAdapter(): AdapterInterface
    {
        $rootPath = dirname(__DIR__, 3);
        (new EnvironmentLoader($rootPath))->load();
        $config = new EnvironmentConfig($rootPath);
        $resolver = new DatabaseResolver($config);
        return $resolver->resolve('mysql.main', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->adapter->isConnected()) {
            $this->fail('MySQL adapter failed to connect.');
        }

        // Initialize Guard 1
        $this->guard1 = new MySQLSecurityGuard($this->adapter, $this->identifierStrategy);

        // Initialize Guard 2 (Same adapter, same config, representing a new request)
        $this->guard2 = new MySQLSecurityGuard($this->adapter, $this->identifierStrategy);
    }

    public function testFailureCountsPersistAcrossInstances(): void
    {
        $guard1 = $this->guard1;
        $guard2 = $this->guard2;

        $this->assertNotNull($guard1);
        $this->assertNotNull($guard2);

        $ip = '10.0.0.10';
        $subject = 'persist_user_' . bin2hex(random_bytes(4));

        $guard1->resetAttempts($ip, $subject);

        $attempt = new LoginAttemptDTO(
            ip: $ip,
            subject: $subject,
            occurredAt: time(),
            resetAfter: 300
        );

        // Record failure in Guard 1
        $guard1->recordFailure($attempt);

        // Verify count is visible in Guard 2 (simulating next request)
        $count = $guard2->recordFailure($attempt);
        $this->assertSame(2, $count, 'Failure count should persist and increment to 2 in second guard instance');
    }

    public function testBlockPersistsAcrossInstances(): void
    {
        $guard1 = $this->guard1;
        $guard2 = $this->guard2;

        $this->assertNotNull($guard1);
        $this->assertNotNull($guard2);

        $ip = '10.0.0.11';
        $subject = 'persist_block_' . bin2hex(random_bytes(4));

        $guard1->resetAttempts($ip, $subject);
        $guard1->unblock($ip, $subject);

        $blockDTO = new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: BlockTypeEnum::MANUAL,
            expiresAt: 0, // Permanent
            createdAt: time()
        );

        // Block in Guard 1
        $guard1->block($blockDTO);

        // Verify blocked in Guard 2
        $this->assertTrue($guard2->isBlocked($ip, $subject), 'Block state should persist to second guard instance');

        $retrieved = $guard2->getActiveBlock($ip, $subject);
        $this->assertNotNull($retrieved);
        $this->assertSame($subject, $retrieved->subject);
    }
}
