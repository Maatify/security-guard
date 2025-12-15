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
 * MySQLIntegrationFlowTest
 *
 * Verifies the full authenticated login failure and blocking flow using a real MySQL adapter
 * resolved via the system's DatabaseResolver.
 *
 * Flow:
 * Authenticated subject -> Record Failures -> Max Failures Reached -> Block Applied -> Unblock -> Verify Unblock
 */
class MySQLIntegrationFlowTest extends BaseIntegrationV2TestCase
{
    private ?MySQLSecurityGuard $guard = null;

    protected function validateEnvironment(): void
    {
        // STRICT: Environment validation is delegated to DatabaseResolver / EnvironmentLoader.
    }

    protected function createAdapter(): AdapterInterface
    {
        // STRICT: Use DatabaseResolver to fetch the configured MySQL adapter.
        $rootPath = dirname(__DIR__, 3);

        (new EnvironmentLoader($rootPath))->load();

        $config = new EnvironmentConfig($rootPath);
        $resolver = new DatabaseResolver($config);

        // Resolve 'mysql.main' profile with auto-connect enabled
        return $resolver->resolve('mysql.main', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // STRICT: Fail if not connected. No skipping allowed.
        if (!$this->adapter->isConnected()) {
            $this->fail('MySQL adapter (mysql.main) failed to connect. Ensure DSN configuration is valid and MySQL is running.');
        }

        $this->guard = new MySQLSecurityGuard($this->adapter, $this->identifierStrategy);
    }

    public function testAuthenticatedSubjectBlockFlow(): void
    {
        $this->assertNotNull($this->guard, 'Guard should have been initialized in setUp');
        $guard = $this->guard;

        // 1. Setup Identity
        $ip = '10.0.0.5';
        $subject = 'user_sql_' . bin2hex(random_bytes(4));

        // Ensure clean state
        $guard->resetAttempts($ip, $subject);
        $guard->unblock($ip, $subject);

        // 2. Record Login Failures
        $maxFailures = 5;
        $attempt = new LoginAttemptDTO(
            ip: $ip,
            subject: $subject,
            occurredAt: time(),
            resetAfter: 60
        );

        for ($i = 1; $i <= $maxFailures; $i++) {
            $count = $guard->recordFailure($attempt);
            $this->assertSame($i, $count, "Failure count should increment to $i");

            if ($i < $maxFailures) {
                $this->assertFalse($guard->isBlocked($ip, $subject), "Should not be blocked at attempt $i");
            }
        }

        // 3. Trigger Block
        $blockDTO = new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: BlockTypeEnum::AUTO,
            expiresAt: time() + 300,
            createdAt: time()
        );
        $guard->block($blockDTO);

        // Verify Blocked
        $this->assertTrue($guard->isBlocked($ip, $subject), 'Subject should be blocked after applying block');
        $activeBlock = $guard->getActiveBlock($ip, $subject);
        $this->assertNotNull($activeBlock);
        $this->assertSame($subject, $activeBlock->subject);

        // 4. Unblock
        $guard->unblock($ip, $subject);

        // 5. Verify Unblock Success
        $this->assertFalse($guard->isBlocked($ip, $subject), 'Subject should be unblocked');
        $this->assertNull($guard->getActiveBlock($ip, $subject));
    }
}
