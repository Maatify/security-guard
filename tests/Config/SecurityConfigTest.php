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

use InvalidArgumentException;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use PHPUnit\Framework\TestCase;

class SecurityConfigTest extends TestCase
{
    /**
     * @param array<string, mixed> $override
     */
    private function makeDTO(array $override = []): SecurityConfigDTO
    {
        $base = [
            'windowSeconds' => 60,
            'blockSeconds' => 120,
            'maxFailures' => 5,
            'identifierMode' => IdentifierModeEnum::IDENTIFIER_ONLY,
            'keyPrefix' => 'sg:',
            'backoffEnabled' => true,
            'initialBackoffSeconds' => 10,
            'backoffMultiplier' => 2.0,
            'maxBackoffSeconds' => 300,
        ];

        /** @var array{
         *   windowSeconds:int,
         *   blockSeconds:int,
         *   maxFailures:int,
         *   identifierMode:IdentifierModeEnum,
         *   keyPrefix:string,
         *   backoffEnabled:bool,
         *   initialBackoffSeconds:int,
         *   backoffMultiplier:float,
         *   maxBackoffSeconds:int
         * } $merged
         */
        $merged = array_merge($base, $override);

        return new SecurityConfigDTO(
            windowSeconds: $merged['windowSeconds'],
            blockSeconds: $merged['blockSeconds'],
            maxFailures: $merged['maxFailures'],
            identifierMode: $merged['identifierMode'],
            keyPrefix: $merged['keyPrefix'],
            backoffEnabled: $merged['backoffEnabled'],
            initialBackoffSeconds: $merged['initialBackoffSeconds'],
            backoffMultiplier: $merged['backoffMultiplier'],
            maxBackoffSeconds: $merged['maxBackoffSeconds'],
        );
    }


    public function testConstructorNormalizesValues(): void
    {
        $dto = $this->makeDTO(['keyPrefix' => 'myprefix']);
        $config = new SecurityConfig($dto);

        $this->assertSame(60, $config->windowSeconds());
        $this->assertSame(120, $config->blockSeconds());
        $this->assertSame(5, $config->maxFailures());
        $this->assertSame(IdentifierModeEnum::IDENTIFIER_ONLY, $config->identifierMode());
        $this->assertSame('myprefix:', $config->keyPrefix());
        $this->assertTrue($config->backoffEnabled());
    }

    public function testValidationFailsForInvalidWindowSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityConfig($this->makeDTO(['windowSeconds' => 0]));
    }

    public function testValidationFailsForInvalidBlockSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityConfig($this->makeDTO(['blockSeconds' => 0]));
    }

    public function testValidationFailsForInvalidMaxFailures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityConfig($this->makeDTO(['maxFailures' => 0]));
    }

    public function testValidationFailsForInvalidInitialBackoff(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityConfig($this->makeDTO(['initialBackoffSeconds' => 0]));
    }

    public function testValidationFailsForInvalidBackoffMultiplier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityConfig($this->makeDTO(['backoffMultiplier' => 0.5]));
    }

    public function testValidationFailsForInvalidMaxBackoffSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SecurityConfig($this->makeDTO([
            'initialBackoffSeconds' => 20,
            'maxBackoffSeconds'     => 10
        ]));
    }

    public function testBackoffDisabledReturnsBlockSeconds(): void
    {
        $dto = $this->makeDTO(['backoffEnabled' => false]);
        $config = new SecurityConfig($dto);

        $this->assertSame(120, $config->computeBackoffSeconds(10));
    }

    public function testBackoffBeforeThresholdReturnsBlockSeconds(): void
    {
        $config = new SecurityConfig($this->makeDTO());
        $this->assertSame(120, $config->computeBackoffSeconds(3)); // < maxFailures
    }

    public function testBackoffCalculation(): void
    {
        $dto = $this->makeDTO([
            'initialBackoffSeconds' => 10,
            'backoffMultiplier'     => 2.0, // exponential
            'maxBackoffSeconds'     => 300,
            'maxFailures'           => 3
        ]);

        $config = new SecurityConfig($dto);

        // failureCount = 3 → base
        $this->assertSame(10, $config->computeBackoffSeconds(3));

        // failureCount = 4 → 10 * 2 = 20
        $this->assertSame(20, $config->computeBackoffSeconds(4));

        // failureCount = 5 → 10 * 2^2 = 40
        $this->assertSame(40, $config->computeBackoffSeconds(5));
    }

    public function testBackoffClampedAtMax(): void
    {
        $dto = $this->makeDTO([
            'initialBackoffSeconds' => 50,
            'backoffMultiplier'     => 10,
            'maxBackoffSeconds'     => 100,
            'maxFailures'           => 2,
        ]);

        $config = new SecurityConfig($dto);

        // failureCount = 4 → 50 * 10^(2) = 5000 → clamped to 100
        $this->assertSame(100, $config->computeBackoffSeconds(4));
    }
}
