<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 01:18
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Event\Dispatcher;

use Throwable;
use Psr\Log\LoggerInterface;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;

/**
 * 📝 PsrLoggerDispatcher
 *
 * Logs all security events through any PSR-3 compatible logger.
 *
 * Example:
 *   $dispatcher = new PsrLoggerDispatcher($monolog);
 *   $guard->setEventDispatcher($dispatcher);
 */
final readonly class PsrLoggerDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function dispatch(SecurityEventDTO $event): void
    {
        try {
            $this->logger->info(
                'security_event',
                $event->jsonSerialize()
            );
        } catch (Throwable) {
            // Never interrupt application flow on logging failure
            // Safety-first policy
        }
    }
}
