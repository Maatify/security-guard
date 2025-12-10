<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Coverage\Config;

use InvalidArgumentException;
use Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum;
use Maatify\SecurityGuard\Config\SecurityConfig;
use Maatify\SecurityGuard\Config\SecurityConfigDTO;
use PHPUnit\Framework\TestCase;

class SecurityConfigTest extends TestCase
{
    private function createDTO(array $overrides = []): SecurityConfigDTO
    {
        $defaults = [
            'windowSeconds' => 60,
            'blockSeconds' => 300,
            'maxFailures' => 5,
            'identifierMode' => IdentifierModeEnum::IP_ONLY,
            'keyPrefix' => 'prefix:',
            'backoffEnabled' => true,
            'initialBackoffSeconds' => 10,
            'backoffMultiplier' => 2.0,
            'maxBackoffSeconds' => 100,
        ];
        $params = array_merge($defaults, $overrides);

        return new SecurityConfigDTO(
            $params['windowSeconds'],
            $params['blockSeconds'],
            $params['maxFailures'],
            $params['identifierMode'],
            $params['keyPrefix'],
            $params['backoffEnabled'],
            $params['initialBackoffSeconds'],
            $params['backoffMultiplier'],
            $params['maxBackoffSeconds']
        );
    }

    public function testConstructAndGetters(): void
    {
        $dto = $this->createDTO(['keyPrefix' => 'test']); // test -> test:
        $config = new SecurityConfig($dto);

        $this->assertSame(60, $config->windowSeconds());
        $this->assertSame(300, $config->blockSeconds());
        $this->assertSame(5, $config->maxFailures());
        $this->assertSame(IdentifierModeEnum::IP_ONLY, $config->identifierMode());
        $this->assertSame('test:', $config->keyPrefix()); // ensures suffix is added
        $this->assertTrue($config->backoffEnabled());
    }

    public function testConstructPrefixNormalization(): void
    {
        $dto = $this->createDTO(['keyPrefix' => 'test:']);
        $config = new SecurityConfig($dto);
        $this->assertSame('test:', $config->keyPrefix());
    }

    public function testComputeBackoffSeconds(): void
    {
        $config = new SecurityConfig($this->createDTO([
            'blockSeconds' => 60,
            'maxFailures' => 3,
            'initialBackoffSeconds' => 10,
            'backoffMultiplier' => 2.0,
            'maxBackoffSeconds' => 100,
        ]));

        // Less than max failures -> returns blockSeconds (but technically not blocked yet usually, but if called returns blockSeconds)
        // Wait, logic: if !backoffEnabled OR failureCount < maxFailures, returns blockSeconds.
        $this->assertSame(60, $config->computeBackoffSeconds(1));
        $this->assertSame(60, $config->computeBackoffSeconds(2));

        // At max failures (failureCount = 3), diff = 0. initial * 2^0 = 10.
        // Wait, if failureCount == maxFailures, it should probably block?
        // Code: $seconds = (int)($initial * ($multiplier ** ($failureCount - $maxFailures)));
        // 3 - 3 = 0. 2^0 = 1. 10 * 1 = 10.
        $this->assertSame(10, $config->computeBackoffSeconds(3));

        // 4 failures. 4 - 3 = 1. 2^1 = 2. 10 * 2 = 20.
        $this->assertSame(20, $config->computeBackoffSeconds(4));

        // 5 failures. 5 - 3 = 2. 2^2 = 4. 10 * 4 = 40.
        $this->assertSame(40, $config->computeBackoffSeconds(5));

        // 6 failures. 6 - 3 = 3. 2^3 = 8. 10 * 8 = 80.
        $this->assertSame(80, $config->computeBackoffSeconds(6));

        // 7 failures. 7 - 3 = 4. 2^4 = 16. 10 * 16 = 160. Max is 100.
        $this->assertSame(100, $config->computeBackoffSeconds(7));
    }

    public function testComputeBackoffDisabled(): void
    {
        $config = new SecurityConfig($this->createDTO(['backoffEnabled' => false, 'blockSeconds' => 300]));
        $this->assertSame(300, $config->computeBackoffSeconds(100));
    }

    public function testValidationWindowSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('windowSeconds must be >= 1');
        new SecurityConfig($this->createDTO(['windowSeconds' => 0]));
    }

    public function testValidationBlockSeconds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('blockSeconds must be >= 1');
        new SecurityConfig($this->createDTO(['blockSeconds' => 0]));
    }

    public function testValidationMaxFailures(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maxFailures must be >= 1');
        new SecurityConfig($this->createDTO(['maxFailures' => 0]));
    }

    public function testValidationBackoffInitial(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('initialBackoffSeconds must be >= 1');
        new SecurityConfig($this->createDTO(['backoffEnabled' => true, 'initialBackoffSeconds' => 0]));
    }

    public function testValidationBackoffMultiplier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('backoffMultiplier must be >= 1.0');
        new SecurityConfig($this->createDTO(['backoffEnabled' => true, 'backoffMultiplier' => 0.5]));
    }

    public function testValidationMaxBackoff(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maxBackoffSeconds must be >= initialBackoffSeconds');
        new SecurityConfig($this->createDTO(['backoffEnabled' => true, 'initialBackoffSeconds' => 10, 'maxBackoffSeconds' => 5]));
    }
}
