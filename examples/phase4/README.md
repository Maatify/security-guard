# 📘 **Maatify Security Guard – Examples (Phase 4)**

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

Practical examples demonstrating how to integrate **`maatify/security-guard`** across three environments:

* **Native PHP**
* **Slim Framework**
* **Laravel Framework**

Examples include:

* Bootstrapping
* Login failure handling
* Block / Unblock
* Event pipelines
* Custom events
* System operations
* Full security flow
* Middleware integration

> All examples assume:
>
> * `maatify/data-adapters` is installed
> * `EnvironmentConfig` is configured
> * Profiles available:
    >
    >   * `redis.security`
>   * `mongo.audit`
>   * `mysql.security`

---

# 🧩 **1️⃣ Bootstrap – Security Guard Service**

| Environment | Example Link                                                                 |
|-------------|------------------------------------------------------------------------------|
| **Native**  | [native/bootstrap_security_guard.php](native/bootstrap_security_guard.php)   |
| **Slim**    | [slim/bootstrap_security_guard.php](slim/bootstrap_security_guard.php)       |
| **Laravel** | [laravel/bootstrap_security_guard.php](laravel/bootstrap_security_guard.php) |

---

# 🔐 **2️⃣ Basic Login Failure Flow**

| Environment | Example Link                                                   |
|-------------|----------------------------------------------------------------|
| **Native**  | [native/login_failed_flow.php](native/login_failed_flow.php)   |
| **Slim**    | [slim/login_failed_flow.php](slim/login_failed_flow.php)       |
| **Laravel** | [laravel/login_failed_flow.php](laravel/login_failed_flow.php) |

---

# 🧩 **Controller-like Example (Laravel)**

A simplified example demonstrating how a Laravel controller would integrate with SecurityGuardService.

| Environment | Example Link                                                                           |
|-------------|----------------------------------------------------------------------------------------|
| **Laravel** | [laravel/basic_controller_like_example.php](laravel/basic_controller_like_example.php) |

---

# 🛡️ **3️⃣ Admin – Manual Block & Unblock**

| Environment | Example Link                                                       |
|-------------|--------------------------------------------------------------------|
| **Native**  | [native/admin_block_unblock.php](native/admin_block_unblock.php)   |
| **Slim**    | [slim/admin_block_unblock.php](slim/admin_block_unblock.php)       |
| **Laravel** | [laravel/admin_block_unblock.php](laravel/admin_block_unblock.php) |

---

# 🔁 **4️⃣ Sync Event Dispatcher**

| Environment | Example Link                                                           |
|-------------|------------------------------------------------------------------------|
| **Native**  | [native/event_sync_dispatcher.php](native/event_sync_dispatcher.php)   |
| **Slim**    | [slim/event_sync_dispatcher.php](slim/event_sync_dispatcher.php)       |
| **Laravel** | [laravel/event_sync_dispatcher.php](laravel/event_sync_dispatcher.php) |

---

# 📜 **5️⃣ PSR-3 Logger Dispatcher**

| Environment | Example Link                                                                                       |
|-------------|----------------------------------------------------------------------------------------------------|
| **Native**  | [native/event_psr_logger_dispatcher.php](native/event_psr_logger_dispatcher.php)                   |
| **Slim**    | [slim/event_psr_logger_dispatcher.php](slim/event_psr_logger_dispatcher.php)                       |
| **Laravel** | [laravel/event_psr_logger_dispatcher_example.php](laravel/event_psr_logger_dispatcher_example.php) |

---

# 🧨 **6️⃣ Custom Security Events**

| Environment | Example Link                                                           |
|-------------|------------------------------------------------------------------------|
| **Native**  | [native/custom_security_event.php](native/custom_security_event.php)   |
| **Slim**    | [slim/custom_security_event.php](slim/custom_security_event.php)       |
| **Laravel** | [laravel/custom_security_event.php](laravel/custom_security_event.php) |

---

# ⚙️ **7️⃣ System Operations**

| Environment | Example Link                                                   |
|-------------|----------------------------------------------------------------|
| **Native**  | [native/system_operations.php](native/system_operations.php)   |
| **Slim**    | [slim/system_operations.php](slim/system_operations.php)       |
| **Laravel** | [laravel/system_operations.php](laravel/system_operations.php) |

---

# 🚨 **8️⃣ Check Block Status**

| Environment | Example Link                                                     |
|-------------|------------------------------------------------------------------|
| **Native**  | [native/check_block_status.php](native/check_block_status.php)   |
| **Slim**    | [slim/check_block_status.php](slim/check_block_status.php)       |
| **Laravel** | [laravel/check_block_status.php](laravel/check_block_status.php) |

---

# 🧵 **9️⃣ Full Security Flow**

| Environment | Example Link                                                     |
|-------------|------------------------------------------------------------------|
| **Native**  | [native/full_security_flow.php](native/full_security_flow.php)   |
| **Slim**    | [slim/full_security_flow.php](slim/full_security_flow.php)       |
| **Laravel** | [laravel/full_security_flow.php](laravel/full_security_flow.php) |

---

# 🧱 **🔟 Framework Middleware Integration**

| Environment | Example Link                                                                     |
|-------------|----------------------------------------------------------------------------------|
| **Slim**    | [slim/slim_middleware_example.php](slim/slim_middleware_example.php)             |
| **Laravel** | [laravel/laravel_middleware_example.php](laravel/laravel_middleware_example.php) |

---

# 📦 **Summary**

Phase 4 provides:

* A unified adapter-driven `SecurityGuardService`
* Rich event pipeline (Sync / PSR-3 / Custom)
* Clear parallel examples for: **Native**, **Slim**, **Laravel**
* Production-ready integration patterns

These examples help developers adopt `maatify/security-guard` smoothly across any PHP environment.

---
