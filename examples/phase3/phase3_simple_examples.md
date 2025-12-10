# Phase 3 — Simple Driver Examples (Beginner Friendly)

These examples introduce the basic concepts of Phase 3 using **pure PHP only**,  
with **RedisSecurityGuard** as the storage backend.

> This file is intentionally simple.  
> It does not cover MySQL or MongoDB.  
> Developers who need framework integration or multi-driver examples  
> should read:  
> **`docs/examples/phase3_driver_usage.md`**

---

# 📦 1. Bootstrap

Before using any driver, the Security Guard library must load:

- Composer autoload  
- Environment configuration  
- Database resolver  
- Redis adapter (from your project's profiles)

```php
<?php

require __DIR__ . '/../../../vendor/autoload.php';

// ---------------------------------------------------------------------
// 🔧 Load adapter configuration
// ---------------------------------------------------------------------
$config = new EnvironmentConfig(__DIR__ . '/../../../');
$resolver = new DatabaseResolver($config);

// ---------------------------------------------------------------------
// 🟢 Resolve Redis adapter (Phase 3 basic examples)
// ---------------------------------------------------------------------
$redisAdapter = $resolver->resolve('redis.security', autoConnect: true);

// Shared logical identifier for all examples
$identifier = 'login.guard';
````

---

# 🧱 2. Create a RedisSecurityGuard Driver

```php
$driver = new RedisSecurityGuard($redisAdapter, $identifier);
```

The `$identifier` ensures all counters, blocks, and statistics
belong to one logical security domain (e.g., login system).

---

# 🔹 3. Record a Failed Login Attempt

```php
$attempt = new LoginAttemptDTO(
    ip: '192.168.1.50',
    subject: 'user@example.com',
    resetAfter: 900,
    userAgent: 'Mozilla/5.0'
);

$count = $driver->recordFailure($attempt);

echo "New failure count: {$count}\n";
```

What happens internally (simplified):

* Redis increments a counter
* Applies/reset TTL (`resetAfter`)
* Returns current attempt count

---

# 🔹 4. Reset Attempts

```php
$driver->resetAttempts(
    ip: '192.168.1.50',
    subject: 'user@example.com'
);

echo "Attempts reset.\n";
```

This clears the counter for the given subject/IP pair.

---

# 🔹 5. Block a User or IP

```php
$block = new SecurityBlockDTO(
    ip: '192.168.1.50',
    subject: 'user@example.com',
    type: BlockTypeEnum::AUTO,
    createdAt: time(),
    expiresAt: time() + 3600 // 1 hour block
);

$driver->block($block);
```

---

# 🔹 6. Remove an Existing Block

```php
$driver->unblock(
    ip: '192.168.1.50',
    subject: 'user@example.com'
);
```

---

# 🔹 7. Check Statistics

```php
$stats = $driver->getStats(
    ip: '192.168.1.50',
    subject: 'user@example.com'
);

print_r($stats);
```

A typical output:

```php
[
    'attempts' => 2,
    'blocked' => true,
    'block_expires_at' => 1733848800
]
```

---

# 📘 Summary (What You Learned)

* How to bootstrap the adapter system
* How to resolve a Redis adapter
* How to instantiate `RedisSecurityGuard`
* How to record failures, reset attempts, block, unblock, and read stats
* All with **pure PHP** and **minimal complexity**

For real-world integration (Slim, Laravel) and full multi-driver examples (MySQL + Redis + MongoDB), continue to:

📄 [`docs/examples/phase3_driver_usage.md`](phase3_driver_usage.md)
