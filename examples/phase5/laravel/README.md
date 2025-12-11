# 📘 **Laravel Examples – Phase 5 (STRICT Edition)**

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

> **High-Level Orchestration Examples (Laravel-Style Execution)**
> Demonstrating Security Guard Phase 5 inside a Laravel-like workflow
> using controllers, middleware, routes, and CLI-style simulation
> **without requiring an actual Laravel installation.**

These examples mirror **real Laravel usage**, but run as standalone PHP files for clarity.

They illustrate:

* `handleAttempt()` orchestration
* Automatic blocking
* Multi-device & multi-flow behavior
* Dynamic config switching
* Laravel-style middleware
* Laravel-style routing
* Event dispatching
* Advanced PRO attack scenarios

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
getStats()
```

---

# 🧭 **Navigation**

* [📂 Directory Overview](#📂-directory-overview)
* [🚀 bootstrap.php](#🚀-1-bootstrapphp)
* [🟦 Basic Examples](#🟦-2-basic-examples)
* [🟩 Config Switching](#🟩-3-config-switching)
* [🟥 Manual + Auto Blocking](#🟥-4-manual--auto-blocking)
* [🌐 Multi-Flow + Multi-Device](#🌐-5-multi-flow--multi-device)
* [📡 Events](#📡-6-events-and-dispatching)
* [🟧 Laravel Integration Layer](#🟧-7-laravel-style-integration)
* [🔥 PRO Examples](#🔥-8-pro-examples-advanced-scenarios)
* [📌 Strict Notes](#📌-important-notes-strict-mode)
* [🧩 How to Run](#🧩-how-to-run-the-examples)

---

# 📂 **Directory Overview**

```
examples/
└── phase5/
    └── laravel/
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

This file configures the Laravel-style environment:

* Loads DataAdapters environment (`EnvironmentConfig`)
* Resolves the `redis.security` connection profile
* Builds a fully populated **SecurityConfigDTO (9 params)**
* Creates the IdentifierStrategy
* Creates a SecurityGuardService instance
* Returns `$guard`

Usage:

```php
$guard = require __DIR__ . '/bootstrap.php';
```

---

# 🟦 **2. Basic Examples**

## ▶ [`example_basic.php`](example_basic.php)

Demonstrates:

* failed login → counter increments
* successful login → counters reset
* Laravel-style simulated request handling

---

## ▶ [`example_auto_block.php`](example_auto_block.php)

Shows automatic blocking when:

```
failureCount >= maxFailures
```

Once blocked, the user cannot proceed until:

```
getRemainingBlockSeconds()
```

---

## ▶ [`example_reset_logic.php`](example_reset_logic.php)

A successful login event completely resets the failure state.

---

## ▶ [`example_login_flow.php`](example_login_flow.php)

Full login flow sequence:

```
fail → fail → success → fail
```

Counter resets after the successful attempt.

---

# 🟩 **3. Config Switching**

## ▶ [`example_custom_config.php`](example_custom_config.php)

Simulates two independent security policies:

| Flow     | window | block | maxFailures | keyPrefix |
|----------|--------|-------|-------------|-----------|
| Admin    | 20s    | 900s  | 3           | admin:    |
| Customer | 60s    | 300s  | 5           | cust:     |

Switched via:

```php
$guard->setConfig($adminConfig);
$guard->setConfig($customerConfig);
```

---

# 🟥 **4. Manual + Auto Blocking**

## ▶ [`example_manual_block.php`](example_manual_block.php)

Admin operations:

* Manual block with `SecurityBlockDTO`
* Manual unblock
* Checking block status with:

  ```
  isBlocked()
  getRemainingBlockSeconds()
  ```

---

# 🌐 **5. Multi-Flow + Multi-Device**

## ▶ [`example_multi_flows.php`](example_multi_flows.php)

Demonstrates isolation:

* different IPs
* different subjects
* different flows

Each maintains its own window and counters.

---

# 📡 **6. Events and Dispatching**

## ▶ [`example_events.php`](example_events.php)

Shows:

* Attaching a custom event dispatcher via

  ```php
  $guard->setEventDispatcher(new class { ... });
  ```
* Receiving full `SecurityEventDTO` objects
* Logging activity from multiple flows

Event types include:

* login attempt
* block created
* block removed
* cleanup

---

# 🟧 **7. Laravel-Style Integration**

## ▶ [`example_middleware.php`](example_middleware.php)

Shows a simulated PSR-like middleware class that:

* blocks requests early
* runs login attempts
* simulates route context
* returns array responses

Uses only:

```
handleAttempt()
isBlocked()
getRemainingBlockSeconds()
```

---

## ▶ [`example_routes.php`](example_routes.php)

Laravel-style routing simulation for:

* `/login`
* `/admin/login`
* `/api/token`

Each route:

* constructs DTO
* passes to `handleAttempt()`
* handles success/failure
* handles auto-block
* supports admin custom policy

---

# 🔥 **8. PRO Examples (Advanced Scenarios)**

## ▶ [`pro/adaptive_backoff_simulation.php`](pro/adaptive_backoff_simulation.php)

Simulates:

* progressive backoff escalation
* auto-block when a threshold is exceeded
* pure internal Phase 5 logic

---

## ▶ [`pro/analytics_dashboard_simulation.php`](pro/analytics_dashboard_simulation.php)

Uses:

```php
$stats = $guard->getStats();
```

Useful for:

* dashboards
* audit panels
* real-time analytics
* intrusion detection UI

---

## ▶ [`pro/brute_force_simulation.php`](pro/brute_force_simulation.php)

Attacker repeatedly tries login → Phase 5 escalates counters → auto-block.

---

## ▶ [`pro/distributed_attack.php`](pro/distributed_attack.php)

Simulates:

* botnet attack
* multiple IPs
* same subject

Phase 5 blocks **the user**, not the IP.

---

## ▶ [`pro/multi_device_security.php`](pro/multi_device_security.php)

Simulates suspicious devices hitting the same account:

* desktop
* mobile
* tablet
* unknown device

Perfect for account-hijack detection.

---

# 📌 **Important Notes (STRICT Mode)**

✔ No direct interaction with drivers (Redis, Mongo, MySQL).
✔ No manual TTL or key manipulation.
✔ No custom backoff logic — use internal Phase 5 logic only.
✔ Always build DTOs using `LoginAttemptDTO::now()`.
✔ Always set:

```php
resetAfter = $guard->getConfig()->windowSeconds();
```

✔ The config setter/getter is the only valid way to override behavior.

---

# 🧩 **How to Run the Examples**

1. Install dependencies:

```
composer install
```

2. Ensure Redis profile for Security Guard is available:

```
redis.security
```

3. Execute any Laravel-style example:

```
php examples/phase5/laravel/example_basic.php
```

Or run a PRO example:

```
php examples/phase5/laravel/pro/brute_force_simulation.php
```

---