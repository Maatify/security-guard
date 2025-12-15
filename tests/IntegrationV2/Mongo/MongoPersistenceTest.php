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

namespace Maatify\SecurityGuard\Tests\IntegrationV2\Mongo;

use Maatify\Bootstrap\Core\EnvironmentLoader;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\SecurityGuard\Drivers\Mongo\MongoSecurityGuard;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Tests\IntegrationV2\BaseIntegrationV2TestCase;

/**
 * MongoPersistenceTest
 *
 * Verifies that state is persisted across multiple guard instances using real Mongo.
 */
class MongoPersistenceTest extends BaseIntegrationV2TestCase
{
    protected function validateEnvironment(): void
    {
    }

    protected function createAdapter(): AdapterInterface
    {
        $rootPath = dirname(__DIR__, 3);
        (new EnvironmentLoader($rootPath))->load();
        $config = new EnvironmentConfig($rootPath);
        $resolver = new DatabaseResolver($config);
        return $resolver->resolve('mongo.main', true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (!$this->adapter->isConnected()) {
            $this->fail('Mongo adapter (mongo.main) failed to connect.');
        }
    }

    public function testPersistenceAcrossInstances(): void
    {
        $ip = '10.0.0.7';
        $subject = 'user_mongo_persist_' . bin2hex(random_bytes(4));

        // Instance 1
        $guard1 = new MongoSecurityGuard($this->adapter, $this->identifierStrategy);

        // Clear State
        $guard1->unblock($ip, $subject);
        $guard1->resetAttempts($ip, $subject);

        // Record Failure on Guard 1
        $attempt = new LoginAttemptDTO(
            ip: $ip,
            subject: $subject,
            occurredAt: time(),
            resetAfter: 60
        );
        $count = $guard1->recordFailure($attempt);
        $this->assertSame(1, $count);

        // Instance 2
        $guard2 = new MongoSecurityGuard($this->adapter, $this->identifierStrategy);

        // Guard 2 should see the failure (count should increment to 2)
        $count2 = $guard2->recordFailure($attempt);
        $this->assertSame(2, $count2, 'Guard 2 should see previous attempts and increment count to 2');

        // Block on Guard 2
        $blockDTO = new SecurityBlockDTO(
            ip: $ip,
            subject: $subject,
            type: BlockTypeEnum::SYSTEM,
            expiresAt: time() + 600,
            createdAt: time()
        );
        $guard2->block($blockDTO);

        // Guard 1 should see the block
        $this->assertTrue($guard1->isBlocked($ip, $subject), 'Guard 1 should reflect block applied by Guard 2');

        $activeBlock = $guard1->getActiveBlock($ip, $subject);
        $this->assertNotNull($activeBlock);
        $this->assertSame(BlockTypeEnum::SYSTEM, $activeBlock->type);

        // Cleanup
        $guard1->unblock($ip, $subject);
    }
}
