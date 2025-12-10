<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 00:44
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Library on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\SecurityGuard\Event\Contracts;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;

/**
 * 📨 Event Dispatcher Contract
 *
 * Optional interface applications may be implemented to receive security events.
 * SecurityGuardService will use this ONLY if provided.
 */
interface EventDispatcherInterface
{
    public function dispatch(SecurityEventDTO $event): void;
}
