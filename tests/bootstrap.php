<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-08 04:34
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\Bootstrap\Core\EnvironmentLoader;

/**
 * 🧩 **Environment Bootstrapping Script**
 *
 * 🎯 **Purpose: **
 * Provides a minimal executable test script to validate environment
 * loading functionality via {@see EnvironmentLoader}.
 *
 * 🧠 **Behavior: **
 * - Loads environment variables from the `.env` file located at the project root.
 * - Ensures that configuration values are correctly parsed and stored in `$_ENV`.
 * - Prints the currently active application environment (APP_ENV).
 *
 * ✅ **Usage: **
 * ```bash
 * php tests/bootstrap.php
 * ```
 * Expected output:
 * ```
 * 🧪 Environment: development
 * ```
 */

// Override Predis with a fake client BEFORE autoload loads original Predis classes
require __DIR__ . '/Fake/FakePredisClient.php';

// ------------------------------------------------------------
// 1) Load composer autoload
// ------------------------------------------------------------
$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($autoload)) {
    fwrite(STDERR, "❌ Autoload not found: $autoload" . PHP_EOL);
    exit(1);
}

require_once $autoload;

// ------------------------------------------------------------
// 2) Load environment variables (testing/default)
// ------------------------------------------------------------
$loader = new EnvironmentLoader(dirname(__DIR__));
$loader->load();

// ------------------------------------------------------------
// 3) Normalize environment value for PHPStan level=max
// ------------------------------------------------------------
$envRaw = $_ENV['APP_ENV'] ?? 'unknown';

/*
 * PHPStan Safe Normalization
 * mixed → string (safe)
 */
$envString = is_scalar($envRaw)
    ? (string) $envRaw
    : 'unknown';

// ------------------------------------------------------------
// 4) Display current environment (deterministic, safe)
// ------------------------------------------------------------
echo '🧪 Environment: ' . $envString . PHP_EOL;

// ------------------------------------------------------------
// 5) Optional: Disable output buffering for CI
// ------------------------------------------------------------
if (function_exists('ini_set')) {
    ini_set('output_buffering', 'off');
    ini_set('implicit_flush', '1');
}
