<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:53
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Config;

use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigLoader;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use PHPUnit\Framework\TestCase;

class SecurityConfigLoaderTest extends TestCase
{
    /**
     * Ensure fromArray() loads all values and casts correctly.
     */
    public function testFromArrayLoadsValuesCorrectly(): void
    {
        $config = SecurityConfigLoader::fromArray([
            'windowSeconds'         => '100',               // string → int
            'blockSeconds'          => 200,
            'maxFailures'           => '7',
            'identifierMode'        => 'identifier_and_ip',
            'keyPrefix'             => 'custom',
            'backoffEnabled'        => 'false',           // string → bool
            'initialBackoffSeconds' => '20',
            'backoffMultiplier'     => '4.5',          // string → float
            'maxBackoffSeconds'     => '999',
        ]);

        $this->assertSame(100, $config->windowSeconds());
        $this->assertSame(200, $config->blockSeconds());
        $this->assertSame(7, $config->maxFailures());
        $this->assertSame(IdentifierModeEnum::IDENTIFIER_AND_IP, $config->identifierMode());
        $this->assertSame('custom:', $config->keyPrefix()); // normalized with trailing colon

        // backoff is disabled → returns blockSeconds
        $this->assertFalse($config->backoffEnabled());
        $this->assertSame(200, $config->computeBackoffSeconds(50));
    }

    /**
     * Ensure missing array keys fall back to default values.
     */
    public function testFromArrayUsesDefaultsWhenMissing(): void
    {
        $config = SecurityConfigLoader::fromArray([]);

        $this->assertSame(900, $config->windowSeconds());
        $this->assertSame(1800, $config->blockSeconds());
        $this->assertSame(5, $config->maxFailures());
        $this->assertSame(IdentifierModeEnum::IDENTIFIER_ONLY, $config->identifierMode());
        $this->assertSame('sg:', $config->keyPrefix());
        $this->assertTrue($config->backoffEnabled());

        // Since failureCount < maxFailures → returns blockSeconds
        $this->assertSame(1800, $config->computeBackoffSeconds(3));
    }

    /**
     * Ensure fromEnv() loads expected values from ENV superglobals.
     */
    public function testFromEnvLoadsValuesCorrectly(): void
    {
        $_ENV = [
            'SG_WINDOW_SECONDS'     => '300',
            'SG_BLOCK_SECONDS'      => '800',
            'SG_MAX_FAILURES'       => '9',
            'SG_IDENTIFIER_MODE'    => 'ip_only',
            'SG_KEY_PREFIX'         => 'envprefix',
            'SG_BACKOFF_ENABLED'    => 'false',
            'SG_BACKOFF_INITIAL'    => '15',
            'SG_BACKOFF_MULTIPLIER' => '2.2',
            'SG_BACKOFF_MAX'        => '777',
        ];

        $config = SecurityConfigLoader::fromEnv();

        $this->assertSame(300, $config->windowSeconds());
        $this->assertSame(800, $config->blockSeconds());
        $this->assertSame(9, $config->maxFailures());
        $this->assertSame(IdentifierModeEnum::IP_ONLY, $config->identifierMode());
        $this->assertSame('envprefix:', $config->keyPrefix());
        $this->assertFalse($config->backoffEnabled());

        // backoff disabled → returns blockSeconds
        $this->assertSame(800, $config->computeBackoffSeconds(10));
    }

    /**
     * Ensure defaults() returns correct default configuration.
     */
    public function testDefaultsReturnsExpectedConfig(): void
    {
        $config = SecurityConfigLoader::defaults();

        $this->assertSame(900, $config->windowSeconds());
        $this->assertSame(1800, $config->blockSeconds());
        $this->assertSame(5, $config->maxFailures());
        $this->assertSame('sg:', $config->keyPrefix());
        $this->assertTrue($config->backoffEnabled());
    }
}
