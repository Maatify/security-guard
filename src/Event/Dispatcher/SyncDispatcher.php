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

use Closure;
use Throwable;
use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;
use Maatify\SecurityGuard\Event\Contracts\EventListenerInterface;

/**
 * ⚡ SyncDispatcher
 *
 * Supports TWO listener types:
 *  1) Closures  → fast inline handlers
 *  2) Objects implementing EventListenerInterface → production listeners
 */
final class SyncDispatcher implements EventDispatcherInterface
{
    /**
     * @var array<int, Closure(SecurityEventDTO):void>
     */
    private array $closureListeners = [];

    /**
     * @var array<int, EventListenerInterface>
     */
    private array $objectListeners = [];

    /**
     * Add a closure listener.
     *
     * @param   Closure(SecurityEventDTO):void  $listener
     */
    public function addClosure(Closure $listener): void
    {
        $this->closureListeners[] = $listener;
    }

    /**
     * Add an object-based listener.
     */
    public function addListener(EventListenerInterface $listener): void
    {
        $this->objectListeners[] = $listener;
    }

    /**
     * Dispatch event to both closure listeners and object listeners.
     */
    public function dispatch(SecurityEventDTO $event): void
    {
        // 1) execute closure listeners
        foreach ($this->closureListeners as $listener) {
            try {
                $listener($event);
            } catch (Throwable) {
                // Safe dispatching: never break
            }
        }

        // 2) execute object listeners
        foreach ($this->objectListeners as $listener) {
            try {
                $listener->handle($event);
            } catch (Throwable) {
                // Safe dispatching: never break
            }
        }
    }
}

