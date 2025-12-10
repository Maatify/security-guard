<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:09
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Illuminate\Http\Request;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

/** @var \Maatify\SecurityGuard\Service\SecurityGuardService $guard */
$guard = require __DIR__ . '/bootstrap_security_guard.php';

// Simulated Laravel request
$request = Request::create('/login', 'POST', [
    'email' => 'attacker@example.com'
]);

$ip      = $request->ip();
$subject = $request->input('email');

// Simulate failed login
$attempt = LoginAttemptDTO::now(
    ip        : $ip,
    subject   : $subject,
    resetAfter: 60
);

$failures = $guard->recordFailure($attempt);

echo "Failures: {$failures}\n";


