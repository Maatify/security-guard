# Phase 3 — Driver Usage Examples (Developer Level)

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

This document provides **practical, real-world usage examples** for all Phase 3 drivers:

- **MySQLSecurityGuard**
- **RedisSecurityGuard**
- **MongoSecurityGuard**

It covers:

- Pure PHP usage  
- Slim Framework integration (basic + advanced)  
- Laravel integration (basic + Service Provider binding)

For a simple, beginner-friendly introduction, see:

📘 `docs/examples/phase3_simple_examples.md`  
For internal behavior specifications, see:

📘 `docs/specs/phase3_driver_behavior.md`

---

# 📦 1. Bootstrap (Shared Across All Drivers)

Every example begins with the following initialization:

```php
<?php

require __DIR__ . '/../../../vendor/autoload.php';

// ---------------------------------------------------------------------
// 🔧 Load adapter configuration
// ---------------------------------------------------------------------
$config = new EnvironmentConfig(__DIR__ . '/../../../');
$resolver = new DatabaseResolver($config);

// ---------------------------------------------------------------------
// 📌 Shared identifier used across all drivers
// ---------------------------------------------------------------------
$identifier = 'login.guard';
```

---

# 🧱 2. Resolving the Adapters

Phase 3 supports three storage backends:

```php
$mysqlAdapter = $resolver->resolve('mysql.security', autoConnect: true);
$redisAdapter = $resolver->resolve('redis.security', autoConnect: true);
$mongoAdapter = $resolver->resolve('mongo.security', autoConnect: true);
```

Each one maps to a different Security Guard driver.

---

# 🚀 3. Instantiating Each Driver

## 🔵 MySQLSecurityGuard

```php
$mysqlGuard = new MySQLSecurityGuard(
    adapter: $mysqlAdapter,
    identifier: $identifier
);
```

---

## 🔴 RedisSecurityGuard

```php
$redisGuard = new RedisSecurityGuard(
    adapter: $redisAdapter,
    identifier: $identifier
);
```

---

## 🟣 MongoSecurityGuard

```php
$mongoGuard = new MongoSecurityGuard(
    adapter: $mongoAdapter,
    identifier: $identifier
);
```

---

# 🧪 4. Common Operations Across All Drivers

These examples work **identically** across MySQL, Redis, and Mongo.

---

## 4.1 Record Failure

```php
$dto = new LoginAttemptDTO(
    ip: '10.0.0.5',
    subject: 'john@example.com',
    resetAfter: 600,
    userAgent: 'Mozilla/5.0'
);

$count = $redisGuard->recordFailure($dto);

echo "Failure count: {$count}\n";
```

---

## 4.2 Reset Attempts

```php
$mysqlGuard->resetAttempts(
    ip: '10.0.0.5',
    subject: 'john@example.com'
);
```

---

## 4.3 Create a Block

```php
$block = new SecurityBlockDTO(
    ip: '10.0.0.5',
    subject: 'john@example.com',
    type: BlockTypeEnum::AUTO,
    createdAt: time(),
    expiresAt: time() + 1800
);

$mongoGuard->block($block);
```

---

## 4.4 Remove Block

```php
$redisGuard->unblock(
    ip: '10.0.0.5',
    subject: 'john@example.com'
);
```

---

## 4.5 Driver Statistics

```php
$stats = $mysqlGuard->getStats(
    ip: '10.0.0.5',
    subject: 'john@example.com'
);

print_r($stats);
```

---

# ⚡ 5. Slim Framework Integration

Slim offers powerful DI and routing, making it ideal for Security Guard integration.

---

## 5.1 Slim — Minimal Container Binding (S1)

```php
$container->set(SecurityGuardService::class, function () use ($redisAdapter, $identifier) {
    return new SecurityGuardService(
        new RedisSecurityGuard($redisAdapter, $identifier)
    );
});
```

Usage inside a Slim route:

```php
$app->post('/login', function ($request, $response) {
    $guard = $this->get(SecurityGuardService::class);

    // ...
});
```

---

## 5.2 Slim — Full DI and Routing Example (S2)

```php
$container->set(MySQLSecurityGuard::class, function () use ($mysqlAdapter, $identifier) {
    return new MySQLSecurityGuard($mysqlAdapter, $identifier);
});

$container->set(RedisSecurityGuard::class, function () use ($redisAdapter, $identifier) {
    return new RedisSecurityGuard($redisAdapter, $identifier);
});

$container->set(MongoSecurityGuard::class, function () use ($mongoAdapter, $identifier) {
    return new MongoSecurityGuard($mongoAdapter, $identifier);
});

$container->set(SecurityGuardService::class, function ($c) {
    return new SecurityGuardService($c->get(RedisSecurityGuard::class));
});

$app->post('/login', function ($request, $response) {
    $guard = $this->get(SecurityGuardService::class);

    // security logic here

    return $response;
});
```

---

# 🟩 6. Laravel Integration

Laravel's container makes driver binding extremely clean.

---

## 6.1 Laravel — Light Usage (L1)

```php
$guard = app(RedisSecurityGuard::class);
$svc = new SecurityGuardService($guard);
```

---

## 6.2 Laravel — Service Provider Binding (L2)

Create:

📄 `app/Providers/SecurityGuardServiceProvider.php`

```php
class SecurityGuardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(RedisSecurityGuard::class, function () {
            $config = new EnvironmentConfig(base_path());
            $resolver = new DatabaseResolver($config);
            $redis = $resolver->resolve('redis.security', autoConnect: true);

            return new RedisSecurityGuard($redis, 'login.guard');
        });

        $this->app->bind(SecurityGuardService::class, function ($app) {
            return new SecurityGuardService(
                $app->make(RedisSecurityGuard::class)
            );
        });
    }
}
```

Usage:

```php
$svc = resolve(SecurityGuardService::class);
```

---

## 6.3 Laravel — Multi-Driver Binding (L3)

```php
$this->app->bind(MySQLSecurityGuard::class, function () {
    $config = new EnvironmentConfig(base_path());
    $resolver = new DatabaseResolver($config);
    $adapter = $resolver->resolve('mysql.security', autoConnect: true);

    return new MySQLSecurityGuard($adapter, 'login.guard');
});

$this->app->bind(MongoSecurityGuard::class, function () {
    $config = new EnvironmentConfig(base_path());
    $resolver = new DatabaseResolver($config);
    $adapter = $resolver->resolve('mongo.security', autoConnect: true);

    return new MongoSecurityGuard($adapter, 'login.guard');
});
```

---

# 📘 Summary

This file covered:

* Bootstrap + adapters
* How to instantiate all drivers (MySQL, Redis, Mongo)
* Common operations for all storage backends
* Slim (minimal + complete DI)
* Laravel (light + full service provider binding)

For deeper behavior rules, internal details, and determinism guarantees, see:

📄 [`docs/specs/phase3_driver_behavior.md`](phase3_driver_behavior.md)

---