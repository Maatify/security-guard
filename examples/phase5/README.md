# 📘 **Maatify Security Guard – Examples (Phase 5, STRICT Edition)**

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

Practical examples demonstrating **Phase 5 High-Level Orchestration Layer**, including:

* Unified `handleAttempt()` login flow
* Automatic blocking
* Adaptive backoff
* Dynamic policy switching
* Multi-flow & multi-device detection
* Event dispatching
* Manual block & unblock
* Analytics-ready stats
* Slim / Laravel middleware & routes
* PRO simulations (botnets, distributed attacks, hijacking patterns)

All examples use only the **Phase 5 public API**:

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

# 🧩 **1️⃣ Bootstrap – Security Guard (Phase 5)**

| Environment | Example Link                                   |
|-------------|------------------------------------------------|
| **Native**  | [native/bootstrap.php](native/bootstrap.php)   |
| **Slim**    | [slim/bootstrap.php](slim/bootstrap.php)       |
| **Laravel** | [laravel/bootstrap.php](laravel/bootstrap.php) |

Each bootstrap:

* Loads Redis via `data-adapters`
* Creates full 9-parameter `SecurityConfigDTO`
* Creates `DefaultIdentifierStrategy`
* Initializes `SecurityGuardService`
* Applies config via `setConfig()`

---

# 🔐 **2️⃣ Basic Login Attempt Flow**

| Environment | Example                                                |
|-------------|--------------------------------------------------------|
| **Native**  | [native/example_basic.php](native/example_basic.php)   |
| **Slim**    | [slim/example_basic.php](slim/example_basic.php)       |
| **Laravel** | [laravel/example_basic.php](laravel/example_basic.php) |

---

# 🚫 **3️⃣ Automatic Blocking Example**

| Environment | Example                                                          |
|-------------|------------------------------------------------------------------|
| **Native**  | [native/example_auto_block.php](native/example_auto_block.php)   |
| **Slim**    | [slim/example_auto_block.php](slim/example_auto_block.php)       |
| **Laravel** | [laravel/example_auto_block.php](laravel/example_auto_block.php) |

---

# 🔄 **4️⃣ Reset Logic (Success → Clear Counters)**

| Environment | Example                                                            |
|-------------|--------------------------------------------------------------------|
| **Native**  | [native/example_reset_logic.php](native/example_reset_logic.php)   |
| **Slim**    | [slim/example_reset_logic.php](slim/example_reset_logic.php)       |
| **Laravel** | [laravel/example_reset_logic.php](laravel/example_reset_logic.php) |

---

# 🟩 **5️⃣ Custom Policy Switching**

| Environment | Example                                                                |
|-------------|------------------------------------------------------------------------|
| **Native**  | [native/example_custom_config.php](native/example_custom_config.php)   |
| **Slim**    | [slim/example_custom_config.php](slim/example_custom_config.php)       |
| **Laravel** | [laravel/example_custom_config.php](laravel/example_custom_config.php) |

---

# 🌐 **6️⃣ Multi-Flow Handling**

| Environment | Example                                                            |
|-------------|--------------------------------------------------------------------|
| **Native**  | [native/example_multi_flows.php](native/example_multi_flows.php)   |
| **Slim**    | [slim/example_multi_flows.php](slim/example_multi_flows.php)       |
| **Laravel** | [laravel/example_multi_flows.php](laravel/example_multi_flows.php) |

---

# 🟥 **7️⃣ Manual Block / Unblock**

| Environment | Example                                                              |
|-------------|----------------------------------------------------------------------|
| **Native**  | [native/example_manual_block.php](native/example_manual_block.php)   |
| **Slim**    | [slim/example_manual_block.php](slim/example_manual_block.php)       |
| **Laravel** | [laravel/example_manual_block.php](laravel/example_manual_block.php) |

---

# 📡 **8️⃣ Event Dispatching**

| Environment | Example                                                  |
|-------------|----------------------------------------------------------|
| **Native**  | [native/example_events.php](native/example_events.php)   |
| **Slim**    | [slim/example_events.php](slim/example_events.php)       |
| **Laravel** | [laravel/example_events.php](laravel/example_events.php) |

---

# 🧱 **9️⃣ Middleware Integration**

| Environment | Example                                                          |
|-------------|------------------------------------------------------------------|
| **Slim**    | [slim/example_middleware.php](slim/example_middleware.php)       |
| **Laravel** | [laravel/example_middleware.php](laravel/example_middleware.php) |

---

# 🛣 **🔟 Routes Integration**

| Environment | Example                                                  |
|-------------|----------------------------------------------------------|
| **Slim**    | [slim/example_routes.php](slim/example_routes.php)       |
| **Laravel** | [laravel/example_routes.php](laravel/example_routes.php) |

---

# 🔥 **1️⃣1️⃣ PRO Examples (Advanced Security Simulations)**

| Scenario                  | Native                                                                                         | Slim                                                                                       | Laravel                                                                                          |
|---------------------------|------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------|
| **Adaptive Backoff**      | [native/pro/adaptive_backoff_simulation.php](native/pro/adaptive_backoff_simulation.php)       | [slim/pro/adaptive_backoff_simulation.php](slim/pro/adaptive_backoff_simulation.php)       | [laravel/pro/adaptive_backoff_simulation.php](laravel/pro/adaptive_backoff_simulation.php)       |
| **Analytics Dashboard**   | [native/pro/analytics_dashboard_simulation.php](native/pro/analytics_dashboard_simulation.php) | [slim/pro/analytics_dashboard_simulation.php](slim/pro/analytics_dashboard_simulation.php) | [laravel/pro/analytics_dashboard_simulation.php](laravel/pro/analytics_dashboard_simulation.php) |
| **Brute Force Attack**    | [native/pro/brute_force_simulation.php](native/pro/brute_force_simulation.php)                 | [slim/pro/brute_force_simulation.php](slim/pro/brute_force_simulation.php)                 | [laravel/pro/brute_force_simulation.php](laravel/pro/brute_force_simulation.php)                 |
| **Distributed Attack**    | [native/pro/distributed_attack.php](native/pro/distributed_attack.php)                         | [slim/pro/distributed_attack.php](slim/pro/distributed_attack.php)                         | [laravel/pro/distributed_attack.php](laravel/pro/distributed_attack.php)                         |
| **Multi-Device Security** | [native/pro/multi_device_security.php](native/pro/multi_device_security.php)                   | [slim/pro/multi_device_security.php](slim/pro/multi_device_security.php)                   | [laravel/pro/multi_device_security.php](laravel/pro/multi_device_security.php)                   |

---

# 📌 **Strict Mode Notes**

✔ Only Phase 5 public API
✔ DO NOT touch drivers
✔ DTO must include full 9 parameters
✔ `resetAfter == windowSeconds()`
✔ Always use `setConfig()` / `getConfig()`
✔ Events must be dispatched via dispatcher only
✔ No manual backoff, no custom counters

---

# 🧩 **How to Run Examples**

Install dependencies:

```
composer install
```

Run any native example:

```
php examples/phase5/native/example_basic.php
```

Run Slim:

```
php examples/phase5/slim/example_basic.php
```

Run Laravel Sim examples:

```
php examples/phase5/laravel/example_basic.php
```

---