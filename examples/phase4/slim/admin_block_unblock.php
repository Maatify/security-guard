<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/security-guard
 * @Project     maatify:security-guard
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-10 03:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/security-guard view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use Maatify\SecurityGuard\Enums\BlockTypeEnum;

$guard = require __DIR__ . '/bootstrap_security_guard.php';

$app = AppFactory::create();

// ------------------------------------------------------------
// POST /block
// ------------------------------------------------------------
$app->post('/block', function (ServerRequestInterface $req, ResponseInterface $res) use ($guard) {
    $ip = $req->getParsedBody()['ip'] ?? '127.0.0.1';
    $subject = $req->getParsedBody()['subject'] ?? 'unknown';

    $block = new SecurityBlockDTO(
        ip       : $ip,
        subject  : $subject,
        type     : BlockTypeEnum::MANUAL,
        expiresAt: time() + 3600,
        createdAt: time()
    );

    $guard->block($block);

    $res->getBody()->write("Block applied");

    return $res;
});

// ------------------------------------------------------------
// POST /unblock
// ------------------------------------------------------------
$app->post('/unblock', function (ServerRequestInterface $req, ResponseInterface $res) use ($guard) {
    $ip = $req->getParsedBody()['ip'] ?? '127.0.0.1';
    $subject = $req->getParsedBody()['subject'] ?? 'unknown';

    $guard->unblock($ip, $subject);

    $res->getBody()->write("Unblocked");

    return $res;
});

$app->run();
