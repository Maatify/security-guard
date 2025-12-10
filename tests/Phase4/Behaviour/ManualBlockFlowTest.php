<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Phase4\Behaviour;

require_once __DIR__ . '/../../Fake/FakePredisClient.php';

use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;
use Maatify\SecurityGuard\Service\SecurityGuardService;
use Maatify\SecurityGuard\Tests\Fake\FakeAdapter;
use Maatify\SecurityGuard\Tests\Fake\FakeIdentifierStrategy;
use PHPUnit\Framework\TestCase;

class ManualBlockFlowTest extends TestCase
{
    public function testManualBlockAndUnblockFlow(): void
    {
        $adapter = new FakeAdapter();
        $service = new SecurityGuardService($adapter, new FakeIdentifierStrategy());

        // 1. Manually block
        $block = new SecurityBlockDTO(
            '192.168.0.1',
            'spammer',
            BlockTypeEnum::MANUAL,
            time() + 3600,
            time()
        );

        $service->block($block);

        $this->addToAssertionCount(1);

        // 2. Unblock
        $service->unblock('192.168.0.1', 'spammer');
        $this->addToAssertionCount(1);
    }
}
