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

namespace Maatify\SecurityGuard\Tests\IntegrationV2;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use PHPUnit\Framework\TestCase;

/**
 * BaseIntegrationV2TestCase
 *
 * Foundation for V2 integration tests enforcing:
 * - Real infrastructure (via Adapters)
 * - Real Identifier Strategies
 * - Strict Environment Validation
 * - Safe Cleanup (No FlushAll)
 */
abstract class BaseIntegrationV2TestCase extends TestCase
{
    protected AdapterInterface $adapter;
    protected SecurityConfig $config;
    protected DefaultIdentifierStrategy $identifierStrategy;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Strict Environment Validation
        $this->validateEnvironment();

        // 2. Initialize Infrastructure Adapter
        $this->adapter = $this->createAdapter();

        // 3. Forbid Fake Adapters & Direct Clients
        $this->ensureRealAdapter($this->adapter);

        // 4. Initialize Real Security Config & Identifier Strategy
        // We use a safe, unique prefix for this test run to allow concurrent execution
        // without stepping on toes.
        $testPrefix = $this->generateUniquePrefix();

        $configDTO = new SecurityConfigDTO(
            windowSeconds: 60,
            blockSeconds: 300,
            maxFailures: 5,
            identifierMode: IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix: $testPrefix,
            backoffEnabled: true,
            initialBackoffSeconds: 2,
            backoffMultiplier: 2.0,
            maxBackoffSeconds: 60
        );

        $this->config = new SecurityConfig($configDTO);
        $this->identifierStrategy = new DefaultIdentifierStrategy($this->config);
    }

    /**
     * Factories must implement this to return a valid AdapterInterface.
     * This adapter MUST connect to real infrastructure (Redis/MySQL).
     */
    abstract protected function createAdapter(): AdapterInterface;

    /**
     * Enforces strict environment variable checks.
     * Tests should fail explicitly if infrastructure config is missing.
     */
    protected function validateEnvironment(): void
    {
        // Example: If the child test targets Redis, it should check REDIS_HOST/PORT
        // This method can be overridden by child classes to add specific checks,
        // but base checks can go here if universal.
    }

    protected function requireEnv(string $key): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            $this->fail(sprintf(
                'Integration Test Failure: Missing required environment variable "%s".',
                $key
            ));
        }
        return $value;
    }

    private function ensureRealAdapter(AdapterInterface $adapter): void
    {
        // Forbid known fake adapters
        if ($adapter instanceof FakeAdapter) {
            $this->fail('IntegrationV2 tests MUST NOT use FakeAdapter. Real infrastructure is required.');
        }

        // Check if the adapter exposes the raw driver and forbid direct raw client usage
        // if it bypasses the adapter contract (though the adapter itself is the interface).
        // The requirement is "Forbid direct Redis / PDO / Mongo clients" in the TEST code.
        // The adapter *wraps* them. We just ensure we are using the Adapter abstraction.

        // We can check if the class name contains "Fake" or "Mock" as a heuristic
        if (str_contains(get_class($adapter), 'Fake') || str_contains(get_class($adapter), 'Mock')) {
             $this->fail(sprintf(
                 'IntegrationV2 tests forbid Fake/Mock adapters. Found: %s',
                 get_class($adapter)
             ));
        }
    }

    private function generateUniquePrefix(): string
    {
        // Generates a unique prefix based on test class, method, and random entropy
        // to ensure isolation without global flushes.
        return sprintf(
            'test:%s:%s:%s:',
            str_replace('\\', '_', static::class),
            $this->getName(false),
            bin2hex(random_bytes(4))
        );
    }

    /**
     * Safe cleanup helper.
     * Implementations should use this to clean up keys with the generated prefix,
     * NOT flush the whole database.
     */
    protected function tearDown(): void
    {
        // In a real scenario, we might want to clean up the keys created by this test.
        // Since we don't know the exact driver implementation here (could be SQL, Redis),
        // we leave the specific cleanup logic to the child class or the driver wrapper,
        // but we enforce the *policy* of no global flush by not providing it.

        parent::tearDown();
    }
}
