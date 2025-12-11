# 📘 **Slim Examples – Phase 5 (STRICT Edition)**

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

> **High-Level Orchestration Examples for maatify/security-guard – Slim Framework Integration**

This directory provides **production-grade Slim examples** demonstrating how to use
**Security Guard – Phase 5**, including:

* High-level orchestration
* Multi-flow behaviour
* Automatic blocking + adaptive backoff
* Dynamic security config switching
* Event dispatching
* Middleware integration
* Route-level security
* Advanced PRO scenarios (distributed attacks, device intelligence, dashboards)

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
* [🟩 Slim Integration Layer](#🟩-3-slim-integration-layer)
* [🔥 PRO Advanced Scenarios](#🔥-4-pro-examples-advanced-security-scenarios)
* [📌 Strict Mode Notes](#📌-important-notes-strict-mode)
* [🧩 How to Run](#🧩-how-to-run-the-examples)

---

# 📝 **About Slim Integration**

These examples are built on **Slim Framework 4.x (PSR-7 / PSR-15 / PSR-17)**
and PHP **8.3+**, fully aligned with Phase 5 architecture.

Slim represents a real micro-framework production environment, so the examples show:

* how to connect SecurityGuardService to DI container
* how middleware should behave
* how API routes interact with the security engine
* how events/logging/config switching are applied in real apps

---

# 📂 **Directory Overview**

```
examples/
└── phase5/
    └── slim/
        ├── bootstrap.php
        ├── example_basic.php
        ├── example_auto_block.php
        ├── example_reset_logic.php
        ├── example_custom_config.php
        ├── example_login_flow.php
        ├── example_multi_flows.php
        ├── example_manual_block.php
        ├── example_events.php
        ├── example_middleware.php
        ├── example_routes.php
        └── pro/
            ├── adaptive_backoff_simulation.php
            ├── analytics_dashboard_simulation.php
            ├── brute_force_simulation.php
            ├── distributed_attack.php
            └── multi_device_security.php
```

---

# 🚀 **1. bootstrap.php**

Creates and registers:

* `SecurityGuardService`
* Redis security adapter (via DataAdapters)
* DefaultIdentifierStrategy
* Default SecurityConfig (9-parameter DTO)

All Slim examples load Security Guard via:

```php
$app = require __DIR__ . '/bootstrap.php';
$guard = $app->getContainer()->get(SecurityGuardService::class);
```

⚠ **Important:** SecurityGuardService MUST always be fetched via DI container, not manually instantiated.

---

# 🟦 **2. Basic Examples**

## ▶ [`example_basic.php`](example_basic.php)

Demonstrates a simple:

```
success → reset
failure → increment
```

via:

```php
$count = $guard->handleAttempt($dto, false);
```

---

## ▶ [`example_auto_block.php`](example_auto_block.php)

Shows automatic blocking after surpassing `maxFailures()`.

---

## ▶ [`example_reset_logic.php`](example_reset_logic.php)

Demonstrates how counters reset upon success.

---

## ▶ [`example_custom_config.php`](example_custom_config.php)

Switches between two runtime configs:

* Admin rules
* Customer rules

via:

```php
$guard->setConfig($customConfig);
```

---

## ▶ [`example_login_flow.php`](example_login_flow.php)

A realistic login sequence:

```
fail → fail → success → fail
```

Where success resets count.

---

## ▶ [`example_multi_flows.php`](example_multi_flows.php)

Simulates 3 independent login flows:

* Same IP, different subjects
* Different IPs, same subjects
* ResetAfter per flow

---

## ▶ [`example_manual_block.php`](example_manual_block.php)

Admin-triggered security blocks using native Phase 5:

```php
$guard->block(new SecurityBlockDTO(...));
```

---

## ▶ [`example_events.php`](example_events.php)

Shows connecting a custom `EventDispatcherInterface` to observe:

* login failures
* block creation
* block removal
* cleanup events

---

# 🟩 **3. Slim Integration Layer**

## ▶ [`example_middleware.php`](example_middleware.php)

A PSR-15 middleware that performs:

1. **pre-block check** (early exit)
2. logs failed attempts
3. passes traffic only if allowed
4. integrates with handleAttempt() strictly

Uses:

```
isBlocked()
getRemainingBlockSeconds()
handleAttempt()
```

---

## ▶ [`example_routes.php`](example_routes.php)

Production-ready Slim routes:

* `/login`
* `/admin/login`
* `/api/token`

Each route:

* builds its own LoginAttemptDTO
* respects Phase 5 config
* performs strict block checks
* uses request metadata as context

---

# 🔥 **4. PRO Examples (Advanced Security Scenarios)**

## ▶ [`pro/adaptive_backoff_simulation.php`](pro/adaptive_backoff_simulation.php)

Simulates exponential backoff escalation with:

* initialBackoffSeconds
* multiplier
* maxBackoffSeconds

---

## ▶ [`pro/analytics_dashboard_simulation.php`](pro/analytics_dashboard_simulation.php)

Extracts real-time statistics from:

```php
$stats = $guard->getStats();
```

Use-case: dashboards, monitoring, threat analytics.

---

## ▶ [`pro/brute_force_simulation.php`](pro/brute_force_simulation.php)

Simulates high-frequency failures from one attacker.

---

## ▶ [`pro/distributed_attack.php`](pro/distributed_attack.php)

Simulates botnet attacks (multiple IPs → same subject).
Phase 5 identifies & blocks the victim subject.

---

## ▶ [`pro/multi_device_security.php`](pro/multi_device_security.php)

Simulates login attempts from multiple devices to analyze:

* behavioral anomalies
* device fingerprint context
* high-risk switching

---

# 📌 **Important Notes (STRICT Mode)**

✔ Never call underlying drivers (Redis, MySQL, Mongo).
✔ Never manually track counters or backoff.
✔ Always use the **9-parameter SecurityConfigDTO**:

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

✔ `resetAfter` must match:

```php
$guard->getConfig()->windowSeconds()
```

✔ All DTOs must be created through their official constructors.

✔ NEVER instantiate the service manually — always via Slim DI.

---

# 🧩 **How to Run the Examples**

1. Install dependencies:

```
composer install
```

2. Ensure Redis security profile is active:

```
redis.security
```

3. Execute any Slim example:

```
php examples/phase5/slim/example_basic.php
```
