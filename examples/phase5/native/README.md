# 📘 **Native Examples – Phase 5 (STRICT Edition)**

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

> **High-Level Orchestration Examples (Native, Pure PHP, No Framework)**
> Demonstrating Security Guard Phase 5: blocking, backoff, multi-flows, events, manual blocks, distributed attacks, and more.

These examples provide the **pure PHP implementation** of Phase 5 behavior without any external framework layer.

They illustrate:

* The real API that applications must use
* Phase 5 logic (`handleAttempt()`) and orchestration
* Automatic blocking
* Multi-flow analysis
* Dynamic config switching
* Event dispatching
* Advanced simulations (botnets, backoff, analytics)

All examples strictly use Phase 5 public API only:

```
handleAttempt()
isBlocked()
getRemainingBlockSeconds()
block()
unblock()
cleanup()
setConfig()
getConfig()
```

---

# 🧭 **Navigation**

* [📂 Directory Overview](#📂-directory-overview)
* [🚀 bootstrap.php](#🚀-1-bootstrapphp)
* [🟦 Basic Examples](#🟦-2-basic-examples)
* [🟩 Config Switching](#🟩-3-config-switching)
* [🟥 Manual + Automatic Blocks](#🟥-4-manual--auto-blocking)
* [🌐 Multi-Flow + Multi-Device](#🌐-5-multi-flow--multi-device)
* [📡 Events](#📡-6-events-and-dispatching)
* [🔥 PRO Examples](#🔥-7-pro-examples-advanced-scenarios)
* [📌 Strict Notes](#📌-important-notes-strict-mode)
* [🧩 How to Run](#🧩-how-to-run-the-examples)

---

# 📂 **Directory Overview**

```
examples/
└── phase5/
    └── native/
        ├── bootstrap.php
        ├── example_basic.php
        ├── example_auto_block.php
        ├── example_reset_logic.php
        ├── example_custom_config.php
        ├── example_login_flow.php
        ├── example_multi_flows.php
        ├── example_manual_block.php
        ├── example_events.php
        └── pro/
            ├── adaptive_backoff_simulation.php
            ├── analytics_dashboard_simulation.php
            ├── brute_force_simulation.php
            ├── distributed_attack.php
            └── multi_device_security.php
```

---

# 🚀 **1. bootstrap.php**

Responsible for:

* Loading DataAdapters EnvironmentConfig
* Resolving the `redis.security` profile
* Creating a **full SecurityConfigDTO (9 parameters)**
* Creating the IdentifierStrategy
* Creating the SecurityGuardService
* Attaching default config through `setConfig()`

Used by all examples:

```php
$guard = require __DIR__ . '/bootstrap.php';
```

---

# 🟦 **2. Basic Examples**

## ▶ [`example_basic.php`](example_basic.php)

A simple demonstration of:

* failure → count increments
* success → counter resets

Using:

```php
$result = $guard->handleAttempt($dto, false);
```

---

## ▶ [`example_auto_block.php`](example_auto_block.php)

Shows automatic blocking once:

```
failureCount >= maxFailures
```

---

## ▶ [`example_reset_logic.php`](example_reset_logic.php)

Shows that a successful login **completely resets** the failure counter.

---

## ▶ [`example_login_flow.php`](example_login_flow.php)

A realistic login sequence:

```
fail → fail → success → fail
```

Count resets after the successful attempt.

---

# 🟩 **3. Config Switching**

## ▶ [`example_custom_config.php`](example_custom_config.php)

Demonstrates using **two policies**:

* Admin
* Customer

via:

```php
$guard->setConfig($adminConfig);
$guard->setConfig($customerConfig);
```

Each flow produces different blocking thresholds.

---

# 🟥 **4. Manual + Auto Blocking**

## ▶ [`example_manual_block.php`](example_manual_block.php)

Admin-side example using:

```php
$guard->block(new SecurityBlockDTO(...));
```

plus:

* Manual unblock
* Reading remaining block seconds

---

# 🌐 **5. Multi-Flow + Multi-Device**

## ▶ [`example_multi_flows.php`](example_multi_flows.php)

Simulates multiple independent flows:

* Multiple IPs
* Multiple subjects
* Each with its own window + counters

---

# 📡 **6. Events and Dispatching**

## ▶ [`example_events.php`](example_events.php)

Shows:

* Implementing a custom EventDispatcherInterface
* Receiving SecurityEventDTO objects
* Logging event data for analysis

Event types include:

* `login_attempt`
* `auto_block_created`
* `manual_block_created`
* `block_removed`
* `cleanup`

---

# 🔥 **7. PRO Examples (Advanced Scenarios)**

## ▶ [`pro/adaptive_backoff_simulation.php`](pro/adaptive_backoff_simulation.php)

Simulates exponential lock extension using:

```
initialBackoffSeconds
backoffMultiplier
maxBackoffSeconds
```

---

## ▶ [`pro/analytics_dashboard_simulation.php`](pro/analytics_dashboard_simulation.php)

Demonstrates building analytics from:

```php
$stats = $guard->getStats();
```

Useful for dashboards, monitoring, fraud intelligence.

---

## ▶ [`pro/brute_force_simulation.php`](pro/brute_force_simulation.php)

Simulates a single attacker hammering login endpoints at high speed.

---

## ▶ [`pro/distributed_attack.php`](pro/distributed_attack.php)

Simulates botnet attacks:

* multiple different IPs
* same target subject

Phase 5 properly detects and blocks the **subject**, not the IP.

---

## ▶ [`pro/multi_device_security.php`](pro/multi_device_security.php)

Simulates multiple devices hitting the same account:

* suspicious device switching
* device fingerprint metadata
* context-driven behavior

Perfect for detecting account hijacking attempts.

---

# 📌 **Important Notes (STRICT Mode)**

✔ Never call underlying drivers (Redis, PDO, MongoDB).
✔ Never implement custom counters or backoff logic.
✔ Always create SecurityConfigDTO using 9 parameters:

```
windowSeconds
blockSeconds
maxFailures
identifierMode
keyPrefix
backoffEnabled
initialBackoffSeconds
backoffMultiplier
maxBackoffSeconds
```

✔ `resetAfter` value MUST equal:

```php
$guard->getConfig()->windowSeconds()
```

✔ Always use:

```php
$guard->setConfig()
$guard->getConfig()
```

✔ No direct driver access.
✔ No manual manipulation of storage keys.
✔ Strictly follow Phase 5 orchestration API.

---

# 🧩 **How to Run the Examples**

1. Install dependencies:

```
composer install
```

2. Ensure Redis is running with the correct profile:

```
redis.security
```

3. Run any example:

```
php examples/phase5/native/example_basic.php
```

---