<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-09 03:10
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Service;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Enums\SecurityPlatformEnum;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;
use Maatify\SecurityGuard\Event\SecurityEventFactory;
use Maatify\SecurityGuard\Event\SecurityPlatform;
use Maatify\SecurityGuard\Resolver\SecurityGuardResolver;
use Maatify\SecurityGuard\Identifier\Contracts\IdentifierStrategyInterface;

/**
 * 🎯 SecurityGuardService
 *
 * A unified high-level Facade that provides a clean and consistent API
 * for interacting with the Security Guard engine.
 *
 * Applications can use this service without needing to know
 * which storage backend is being used under the hood.
 */

final class SecurityGuardService
{
    private SecurityGuardDriverInterface $driver;
    private ?EventDispatcherInterface $dispatcher = null;

    public function __construct(
        AdapterInterface $adapter,
        IdentifierStrategyInterface $strategy
    ) {
        $this->driver = (new SecurityGuardResolver())->resolve($adapter, $strategy);
    }

    // -------------------------------------------------------------------------
    //  Public API
    // -------------------------------------------------------------------------

    public function recordFailure(LoginAttemptDTO $dto): int
    {
        $count = $this->driver->recordFailure($dto);

        $event = SecurityEventFactory::fromLoginAttempt(
            dto: $dto,
            platform: SecurityPlatform::fromEnum(SecurityPlatformEnum::WEB) // consumer may override
        );

        $this->dispatchEvent($event);

        return $count;
    }

    public function resetAttempts(string $ip, string $subject): void
    {
        $this->driver->resetAttempts($ip, $subject);
    }

    public function getActiveBlock(string $ip, string $subject): ?SecurityBlockDTO
    {
        return $this->driver->getActiveBlock($ip, $subject);
    }

    public function isBlocked(string $ip, string $subject): bool
    {
        return $this->driver->isBlocked($ip, $subject);
    }

    public function getRemainingBlockSeconds(string $ip, string $subject): ?int
    {
        return $this->driver->getRemainingBlockSeconds($ip, $subject);
    }

    public function block(SecurityBlockDTO $dto): void
    {
        $this->driver->block($dto);

        $event = SecurityEventFactory::blockCreated(
            block: $dto,
            platform: SecurityPlatform::fromEnum(SecurityPlatformEnum::ADMIN)
        );

        $this->dispatchEvent($event);
    }


    public function unblock(string $ip, string $subject): void
    {
        $this->driver->unblock($ip, $subject);

        $event = SecurityEventFactory::blockRemoved(
            ip: $ip,
            subject: $subject,
            platform: SecurityPlatform::fromEnum(SecurityPlatformEnum::ADMIN)
        );

        $this->dispatchEvent($event);
    }


    public function cleanup(): void
    {
        $this->driver->cleanup();

        $event = SecurityEventFactory::cleanup(
            platform: SecurityPlatform::custom('system')
        );

        $this->dispatchEvent($event);
    }


    /**
     * @return array<string,mixed>
     */
    public function getStats(): array
    {
        return $this->driver->getStats();
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    private function dispatchEvent(SecurityEventDTO $event): void
    {
        $this->dispatcher?->dispatch($event);
    }
}
