<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:11
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Illuminate\Http\Request;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

$request = Request::create('/login', 'POST', [
    'email' => 'john@example.com'
]);

$ip = $request->ip();
$subject = $request->input('email');
$userAgent = $request->header('User-Agent');

$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: 60,
    userAgent : $userAgent,
    context   : ['route' => '/login']
);

$count = $guard->recordFailure($attempt);

echo "Failures: {$count}\n";
