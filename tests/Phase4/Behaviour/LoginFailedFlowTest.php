<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Behaviour;

require_once __DIR__ . '/../../Fake/FakePredisClient.php';

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class LoginFailedFlowTest extends TestCase
{
    public function testLoginFailedFlow(): void
    {
        $adapter = new FakeAdapter();
        $service = new SecurityGuardService($adapter, new FakeIdentifierStrategy());

        $dto = new LoginAttemptDTO('10.0.0.1', 'john_doe', time(), 60, null, []);

        $count = $service->recordFailure($dto);
        $this->assertSame(1, $count);

        $count = $service->recordFailure($dto);
        $this->assertSame(2, $count);

        $count = $service->recordFailure($dto);
        $this->assertSame(3, $count);
    }
}
