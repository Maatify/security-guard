# 📘 **Maatify Security Guard – Usage Examples (High-Level Overview)**

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

---

> 📌 **Note (Important)**
> This file provides **high-level usage examples** of the Security Guard engine
> and is intended as an *overview* for developers.
>
> * For **detailed Phase 5 examples (Native + Slim + Laravel)** see:
    >   **[`examples/phase5/README.md`](phase5/README.md)**
>
> * For **Phase 3 driver-level examples**, see:
    >
    >   * [`examples/phase3/phase3_simple_examples.md`](phase3/phase3_simple_examples.md)
>   * [`examples/phase3/phase3_driver_usage.md`](phase3/phase3_driver_usage.md)
>   * [`examples/phase3/phase3_driver_behavior.md`](phase3/phase3_driver_behavior.md)

---

# 🎯 **What This File Covers**

This overview demonstrates:

* How to instantiate the Security Guard engine
* Real vs Fake adapters
* Basic attempt handling
* Resetting counters
* Automatic blocking
* Light-weight simulation patterns
* Environment-based configuration

It does **NOT** include orchestration rules or advanced Phase 5 examples
(those belong in the Phase 5 example suite).

---

# 1️⃣ **Native PHP – Real Security Guard (Redis)**

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

require __DIR__ . '/../vendor/autoload.php';

// Load adapters (from maatify/data-adapters)
$config   = new EnvironmentConfig(__DIR__ . '/../');
$resolver = new DatabaseResolver($config);

// Resolve redis.security profile
$adapter = $resolver->resolve('redis.security', autoConnect: true);

// Build the guard (Phase 5)
$strategy = new \Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy(
    new \Maatify\SecurityGuard\Config\SecurityConfig(
        new \Maatify\SecurityGuard\Config\SecurityConfigDTO(
            windowSeconds        : 60,
            blockSeconds         : 300,
            maxFailures          : 5,
            identifierMode       : \Maatify\SecurityGuard\Config\Enum\IdentifierModeEnum::IDENTIFIER_AND_IP,
            keyPrefix            : 'sg:',
            backoffEnabled       : true,
            initialBackoffSeconds: 300,
            backoffMultiplier    : 2.0,
            maxBackoffSeconds    : 3600,
        )
    )
);

$guard = new SecurityGuardService($adapter, $strategy);

// Example usage
$dto = LoginAttemptDTO::now(
    ip        : '127.0.0.1',
    subject   : 'login',
    resetAfter: 60
);

$result = $guard->handleAttempt($dto, false);

echo "Failure count: {$result}\n";
```

---

# 2️⃣ **Native PHP – Fake Adapter (Testing / CI)**

```php
use Maatify\DataFakes\Resolver\FakeAdapterResolver;
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;
use Maatify\SecurityGuard\Service\SecurityGuardService;

$fakeAdapter = (new FakeAdapterResolver())->resolve('redis');

$strategy = /* build same as real example */;
$guard    = new SecurityGuardService($fakeAdapter, $strategy);

$dto = LoginAttemptDTO::now(
    ip        : 'ip-test-1',
    subject   : 'login',
    resetAfter: 60
);

$count = $guard->handleAttempt($dto, false);

assert($count === 1);
```

### ✔ Recommended for:

* Unit tests
* CI pipelines
* Simulation environments

---

# 3️⃣ **Auto Block Example (High-Level)**

```php
$dto = LoginAttemptDTO::now(
    ip        : '192.168.1.50',
    subject   : 'login',
    resetAfter: 60
);

for ($i = 1; $i <= 6; $i++) {
    $result = $guard->handleAttempt($dto, false);

    if ($guard->isBlocked($dto->ip, $dto->subject)) {
        $remaining = $guard->getRemainingBlockSeconds($dto->ip, $dto->subject);
        echo "⛔ Auto-blocked → {$remaining} seconds remaining\n";
        break;
    }

    echo "Failed attempt {$i} → failure count: {$result}\n";
}
```

---

# 4️⃣ **Reset on Success**

```php
$dto = LoginAttemptDTO::now(
    ip        : '192.168.1.10',
    subject   : 'login',
    resetAfter: 60
);

$guard->handleAttempt($dto, false); // 1
$guard->handleAttempt($dto, false); // 2

// success → reset
$guard->handleAttempt($dto, true);

$guard->handleAttempt($dto, false); // failure count goes back to 1
```

---

# 5️⃣ **Simple Attack Simulation (Conceptual)**

```php
for ($i = 1; $i <= 10; $i++) {
    $dto = LoginAttemptDTO::now(ip: 'bot-ip', subject: 'login', resetAfter: 60);
    $guard->handleAttempt($dto, false);
}
```

Used for:

* diagnosing thresholds
* validating adaptive block durations
* basic brute-force testing

---

# 6️⃣ **Environment Configuration (Phase 5 Compatible)**

```
SG_WINDOW_SECONDS=60
SG_BLOCK_SECONDS=300
SG_MAX_FAILURES=5
SG_IDENTIFIER_MODE=identifier_and_ip
SG_KEY_PREFIX=sg
SG_BACKOFF_ENABLED=true
SG_BACKOFF_INITIAL=300
SG_BACKOFF_MULTIPLIER=2.0
SG_BACKOFF_MAX=3600
```

Load with:

```php
$config = \Maatify\SecurityGuard\Config\SecurityConfigLoader::fromEnv();
$guard->setConfig($config);
```

---

# 7️⃣ **Real vs Fake – Quick Summary**

| Mode     | Purpose                 | Library                 |
|----------|-------------------------|-------------------------|
| **Real** | Production              | `maatify/data-adapters` |
| **Fake** | Tests / CI / Simulation | `maatify/data-fakes`    |

---

# 📚 **Related Documentation**

* Main README → [`README.md`](../README.md)
* Phase 5 Examples → [`phase5/README.md`](phase5/README.md)
* Phase 3 Driver Examples → [`phase3/phase3_simple_examples.md`](phase3/phase3_simple_examples.md)
* Changelog → [`CHANGELOG.md`](../CHANGELOG.md)
* Security Policy → [`SECURITY.md`](../SECURITY.md)

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a></sub>
</p>