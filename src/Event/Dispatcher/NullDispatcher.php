<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 01:17
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Event\Dispatcher;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;
use Maatify\SecurityGuard\Event\Contracts\EventDispatcherInterface;

/**
 * 🚫 NullDispatcher
 *
 * Default no-op dispatcher used when applications do not provide a custom
 * dispatcher. Ensures that event emission never breaks execution.
 */
final class NullDispatcher implements EventDispatcherInterface
{
    public function dispatch(SecurityEventDTO $event): void
    {
        // intentionally do nothing
    }
}
