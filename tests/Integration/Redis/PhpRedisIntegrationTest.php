<?php

declare(strict_types=1);

namespace Maatify\SecurityGuard\Tests\Integration\Redis;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Tests\Drivers\Support\RealRedisAdapter;

class PhpRedisIntegrationTest extends AbstractRedisTestCase
{
    protected function setUp(): void
    {
        // Need extension redis
        $this->requireExtension('redis');
        parent::setUp();
    }

    protected function createAdapter(): AdapterInterface
    {
        // RealRedisAdapter connects to 127.0.0.1:6379 by default
        return new RealRedisAdapter();
    }
}
