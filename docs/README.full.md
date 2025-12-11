# **Maatify Security Guard** – Full Documentation

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

> ⚠️ This file is managed by the Maatify Executor Engine.
> Only sections wrapped with `EXECUTOR_*` markers may be auto-modified.

---

<!-- EXECUTOR_META_START -->

{
"project": "maatify/security-guard",
"php_version": ">=8.4",
"documentation_type": "full",
"managed_by": "executor",
"last_sync": "2025-12-11"
}

<!-- EXECUTOR_META_END -->

---

<!-- EXECUTOR_OVERVIEW_START -->

> This is the extended full documentation for the Maatify Security Guard engine.
> For the short version, see the main [`README.md`](../README.md).

**Maatify Security Guard** is an adaptive, multi-driver security engine designed to protect applications from:

* brute-force attacks
* account abuse
* suspicious authentication behavior
* automated misuse

It provides a unified, deterministic, fake-testable security workflow that integrates cleanly with any PHP application.

Security Guard is part of the **Maatify Ecosystem**, offering:

* Immutable security DTOs
* Unified storage drivers (MySQL / Redis / MongoDB)
* Real vs. Fake execution symmetry
* Full event pipeline (Phase 4)
* High-level security logic & auto-blocking engine (Phase 5)
* Optional dispatchers (sync/logging/custom)
* Ready for future monitoring, auditing, and alerting systems

<!-- EXECUTOR_OVERVIEW_END -->

---

## 📘 Table of Contents

* [Features](#-features)
* [Core Concepts](#-core-concepts)
* [Installation](#-installation)
* [Usage](#-usage)
* [Drivers](#-drivers)
* [Audit System](#-audit-system)
* [Monitoring](#-monitoring)
* [Testing](#-testing)
* [Architecture](#-architecture-overview)
* [Roadmap & Phase Status](#-roadmap--phase-status)
* [Phase Documentation](#-development-phases--documentation-links)
* [License](#-license)
* [Author](#-author)

---

## 🚀 Features

<!-- EXECUTOR_FEATURES_START -->

* Immutable security DTOs (LoginAttemptDTO, SecurityBlockDTO, SecurityEventDTO)
* Extensible action and platform system (SecurityAction, SecurityPlatform)
* Centralized SecurityEventFactory for unified event normalization
* Unified driver contract (SecurityGuardDriverInterface)
* Deterministic adapter-driven architecture
* Real/Fake execution symmetry for drivers
* Full driver layer implemented (Phase 3):

  * MySQL
  * Redis
  * MongoDB
* Complete event system (Phase 4):

  * NullDispatcher
  * SyncDispatcher
  * PsrLoggerDispatcher
  * Custom dispatching pipeline support
* **High-level security engine (Phase 5):**

  * `handleAttempt()` unified login-flow decision engine
  * Automatic blocking after threshold
  * Automatic reset on success
  * Runtime SecurityConfig
  * Integrated event emission for all decisions
* Production + CI-safe behavior
* (Planned — Phase 6) Audit event pipeline
* (Planned — Phase 10–14) Monitoring, webhooks, alerting

<!-- EXECUTOR_FEATURES_END -->

---

## 🧩 Core Concepts

<!-- EXECUTOR_CORE_START -->

* **Immutable DTOs**
  All security structures (`LoginAttemptDTO`, `SecurityBlockDTO`, `SecurityEventDTO`) are fully immutable.

* **Driver-based storage**
  All security state is stored using **maatify/data-adapters**, ensuring real/fake symmetry and deterministic testing.

* **Unified event system (Phase 4)**
  Every security action emits a normalized event through `SecurityEventFactory`.

* **High-level logic (Phase 5)**
  `handleAttempt()` implements:

  * failure counting
  * auto-blocking
  * success reset
  * block checking
  * platform-aware event flow

* **Flexible dispatchers**
  Applications may attach any dispatcher to forward events (sync, async, logs, queue, custom pipelines).

* **Permanent & Temporary Blocking**
  Manual blocks may be indefinite; automatic blocks expire.

* **Symmetry Guarantee**
  All drivers behave identically by contract.

* **Future-ready audit & monitoring pipeline**

<!-- EXECUTOR_CORE_END -->

---

## 📦 Installation

<!-- EXECUTOR_INSTALL_START -->

```bash
composer require maatify/security-guard
```

<!-- EXECUTOR_INSTALL_END -->

---

## 🛠 Usage

### Record a failed login attempt

```php
$svc = new SecurityGuardService($adapter, $identifier);

$dto = LoginAttemptDTO::now(
    ip: '192.168.1.5',
    subject: 'user@example.com',
    resetAfter: 900,
    userAgent: $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
);

$count = $svc->recordFailure($dto);
```

---

### **Phase 5 High-Level Example — handleAttempt()**

```php
$result = $svc->handleAttempt($dto, success: false);

if ($result === null) {
    echo "Login successful — attempts reset.";
} elseif (is_int($result)) {
    echo "Failure count = {$result}";
} else {
    echo "User is blocked — remaining {$result} seconds.";
}
```

---

### Attach a dispatcher

```php
$svc->setEventDispatcher(
    new SyncDispatcher([
        fn(SecurityEventDTO $event) =>
            error_log("SECURITY EVENT: " . json_encode($event))
    ])
);
```

---

### Manual block

```php
$svc->block(
    new SecurityBlockDTO(
        ip: '192.168.1.5',
        subject: 'user@example.com',
        type: BlockTypeEnum::MANUAL,
        expiresAt: time() + 3600,
        createdAt: time(),
    )
);
```

---

### Remove block

```php
$svc->unblock('192.168.1.5', 'user@example.com');
```

---

### Emit a custom event manually

```php
$custom = SecurityEventFactory::custom(
    action: SecurityAction::custom('password_reset'),
    platform: SecurityPlatform::custom('api'),
    ip: '192.168.1.5',
    subject: 'user@example.com',
    context: ['method' => 'email']
);
```

---

## 🧱 Drivers

<!-- EXECUTOR_DRIVERS_START -->

Drivers were fully implemented in **Phase 3** and include:

* **MySQLSecurityGuard**
* **RedisSecurityGuard**
* **MongoSecurityGuard**

All drivers operate exclusively through `maatify/data-adapters`.

<!-- EXECUTOR_DRIVERS_END -->

---

## 🗂 Audit System

<!-- EXECUTOR_AUDIT_START -->

(Planned — Phase 6)

The audit system will include:

* Unified `AuditEventDTO`
* Structured audit persistence layer
* TTL cleanup policies
* Paginated audit history API
* Integration with dispatchers

<!-- EXECUTOR_AUDIT_END -->

---

## 📡 Monitoring

<!-- EXECUTOR_MONITORING_START -->

(Planned — Phase 14)

The monitoring layer will provide:

* Engine health checks
* Driver statistics
* Manual unblock actions
* Observability endpoints
* Webhook bridges

<!-- EXECUTOR_MONITORING_END -->

---

## 🧪 Testing

<!-- EXECUTOR_TESTING_START -->

Security Guard is tested using:

* maatify/data-fakes (deterministic in-memory driver simulation)
* maatify/data-adapters (real integration tests)
* Unified driver behavior tests
* Full DTO + Contract coverage

<!-- EXECUTOR_TESTING_END -->

---

## 🏗 Architecture Overview

<!-- EXECUTOR_ARCH_START -->

```
Application
    ↓
SecurityGuardService (Phase 5 logic)
    ↓
SecurityEventFactory → EventDispatcher (optional)
    ↓
SecurityGuard Drivers (MySQL / Redis / MongoDB)
    ↓
AdapterInterface
    ↓
maatify/data-adapters | maatify/data-fakes
```

<!-- EXECUTOR_ARCH_END -->

---

## 📅 Roadmap & Phase Status

<!-- EXECUTOR_PHASE_TABLE_START -->

| Phase    | Title                                     | Status      | Date       |
|----------|-------------------------------------------|-------------|------------|
| **1**    | Environment Setup                         | ✅ Completed | 2025-12-08 |
| **2**    | Core Architecture & DTOs                  | ✅ Completed | 2025-12-08 |
| **3**    | Driver Implementations                    | ✅ Completed | 2025-12-09 |
| **4**    | Unified Event System & Dispatchers        | ✅ Completed | 2025-12-10 |
| **5**    | High-Level Logic & Auto-Blocking Engine   | ✅ Completed | 2025-12-11 |
| **6**    | Audit System                              | ⏳ Pending   | —          |
| **7–14** | Monitoring, Webhooks, Rate-Limiter Bridge | ⏳ Pending   | —          |

**Current Stable Phase:** Phase 5
**Next Active Phase:** Phase 6

<!-- EXECUTOR_PHASE_TABLE_END -->

---

## 📚 Development Phases & Documentation Links

<!-- EXECUTOR_PHASE_INDEX_START -->

### ✅ Phase 1 — Environment Setup

📄 `docs/phases/README.phase1.md`

---

### ✅ Phase 2 — Core Architecture & DTOs

📄 `docs/phases/README.phase2.md`

---

### ✅ Phase 3 — Driver Implementations

📄 `docs/phases/README.phase3.md`

---

### ✅ Phase 4 — Unified Event System & Dispatchers

📄 `docs/phases/README.phase4.md`

---

### ✅ **Phase 5 — High-Level Logic & Auto-Blocking Engine**

📄 `docs/phases/README.phase5.md`

Delivered:

* Unified login-flow engine (`handleAttempt`)
* Auto-block logic
* Success reset
* Config-driven thresholds
* Integrated event emission
* Custom event routing
* Foundation for analytics

<!-- EXECUTOR_PHASE_INDEX_END -->

---

## 📄 License

MIT License © 2025 Maatify.dev

---

## 👤 Author

**Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))
[https://www.maatify.dev](https://www.maatify.dev)

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a></sub>
</p>