<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 02:31
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);


namespace Maatify\SecurityGuard\Event\Contracts;

use Maatify\SecurityGuard\DTO\SecurityEventDTO;

/**
 * 🔔 EventListenerInterface
 *
 * Implemented by any listener that wants to receive
 * SecurityEventDTO objects from the dispatcher.
 *
 * Examples:
 *  - TelegramAlertListener
 *  - WebhookListener
 *  - AuditMongoListener
 *  - AdminEmailListener
 */
interface EventListenerInterface
{
    /**
     * Handle a dispatched security event.
     */
    public function handle(SecurityEventDTO $event): void;
}