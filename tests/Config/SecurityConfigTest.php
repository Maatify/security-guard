<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:49
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Config;

use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\SecurityConfigLoader;
use PHPUnit\Framework\TestCase;

class SecurityConfigTest extends TestCase
{
    public function testSecurityConfigInitialization(): void
    {
        $dto = new SecurityConfigDTO(
            windowSeconds: 100,
            blockSeconds: 200,
            maxFailures: 3,
            identifierMode: IdentifierModeEnum::IP_ONLY,
            keyPrefix: 'prefix',
            backoffEnabled: true,
            initialBackoffSeconds: 10,
            backoffMultiplier: 2.0,
            maxBackoffSeconds: 100
        );

        $config = new SecurityConfig($dto);

        $this->assertSame(100, $config->windowSeconds());
        $this->assertSame(200, $config->blockSeconds());
        $this->assertSame(3, $config->maxFailures());
        $this->assertSame(IdentifierModeEnum::IP_ONLY, $config->identifierMode());
        $this->assertSame('prefix:', $config->keyPrefix()); // Colon appended
        $this->assertTrue($config->backoffEnabled());
    }

    public function testValidationExceptionsWindowSeconds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('windowSeconds must be >= 1');

        new SecurityConfig(new SecurityConfigDTO(0, 10, 1, IdentifierModeEnum::IP_ONLY, 'p', false, 0, 1.0, 0));
    }

    public function testValidationExceptionsBlockSeconds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('blockSeconds must be >= 1');

        new SecurityConfig(new SecurityConfigDTO(10, 0, 1, IdentifierModeEnum::IP_ONLY, 'p', false, 0, 1.0, 0));
    }

    public function testValidationExceptionsMaxFailures(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxFailures must be >= 1');

        new SecurityConfig(new SecurityConfigDTO(10, 10, 0, IdentifierModeEnum::IP_ONLY, 'p', false, 0, 1.0, 0));
    }

    public function testValidationExceptionsInitialBackoff(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('initialBackoffSeconds must be >= 1');

        new SecurityConfig(new SecurityConfigDTO(10, 10, 1, IdentifierModeEnum::IP_ONLY, 'p', true, 0, 1.0, 10));
    }

    public function testValidationExceptionsBackoffMultiplier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('backoffMultiplier must be >= 1.0');

        new SecurityConfig(new SecurityConfigDTO(10, 10, 1, IdentifierModeEnum::IP_ONLY, 'p', true, 10, 0.9, 10));
    }

    public function testValidationExceptionsMaxBackoff(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxBackoffSeconds must be >= initialBackoffSeconds');

        new SecurityConfig(new SecurityConfigDTO(10, 10, 1, IdentifierModeEnum::IP_ONLY, 'p', true, 10, 2.0, 5));
    }

    public function testComputeBackoffSeconds(): void
    {
        // Disabled backoff
        $dto = new SecurityConfigDTO(10, 300, 3, IdentifierModeEnum::IP_ONLY, 'p', false, 10, 2.0, 100);
        $config = new SecurityConfig($dto);
        $this->assertSame(300, $config->computeBackoffSeconds(5));

        // Enabled backoff
        // maxFailures = 3.
        // failureCount = 3 -> (3-3)=0 exponent. 10 * 1 = 10.
        // failureCount = 4 -> (4-3)=1 exponent. 10 * 2^1 = 20.
        // failureCount = 5 -> (5-3)=2 exponent. 10 * 2^2 = 40.
        $dto2 = new SecurityConfigDTO(10, 300, 3, IdentifierModeEnum::IP_ONLY, 'p', true, 10, 2.0, 100);
        $config2 = new SecurityConfig($dto2);

        $this->assertSame(300, $config2->computeBackoffSeconds(2)); // < maxFailures
        $this->assertSame(10, $config2->computeBackoffSeconds(3));
        $this->assertSame(20, $config2->computeBackoffSeconds(4));
        $this->assertSame(40, $config2->computeBackoffSeconds(5));

        // Max cap
        // failureCount = 10 -> large number -> capped at 100
        $this->assertSame(100, $config2->computeBackoffSeconds(10));
    }

    public function testLoaderDefaults(): void
    {
        $config = SecurityConfigLoader::defaults();
        $this->assertSame(900, $config->windowSeconds());
        $this->assertSame(1800, $config->blockSeconds());
        $this->assertSame('sg:', $config->keyPrefix());
    }

    public function testLoaderFromArray(): void
    {
        $arr = [
            'windowSeconds' => '60',
            'blockSeconds' => 60,
            'maxFailures' => 10,
            'identifierMode' => IdentifierModeEnum::IDENTIFIER_AND_IP->value, // string
            'keyPrefix' => 'test',
            'backoffEnabled' => 'false',
            'initialBackoffSeconds' => 20,
            'backoffMultiplier' => '1.5',
            'maxBackoffSeconds' => 100
        ];

        $config = SecurityConfigLoader::fromArray($arr);
        $this->assertSame(60, $config->windowSeconds());
        $this->assertSame(IdentifierModeEnum::IDENTIFIER_AND_IP, $config->identifierMode());
        $this->assertSame('test:', $config->keyPrefix());
        $this->assertFalse($config->backoffEnabled());
    }

    public function testLoaderFromEnv(): void
    {
        // Mock ENV
        $_ENV['SG_WINDOW_SECONDS'] = '120';
        $_ENV['SG_IDENTIFIER_MODE'] = 'ip_only'; // Fixed: using lowercase value

        $config = SecurityConfigLoader::fromEnv();
        $this->assertSame(120, $config->windowSeconds());
        $this->assertSame(IdentifierModeEnum::IP_ONLY, $config->identifierMode());

        // Cleanup
        unset($_ENV['SG_WINDOW_SECONDS'], $_ENV['SG_IDENTIFIER_MODE']);
    }
}
