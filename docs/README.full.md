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
  "last_sync": null
}
<!-- EXECUTOR_META_END -->

---

<!-- EXECUTOR_OVERVIEW_START -->
> This is the extended full documentation for the Maatify Security Guard engine.  
> For the short version, see the main [`README.md`](../README.md).

**Adaptive multi-driver security engine protecting systems against brute-force, abuse, and suspicious behavior.**

Security Guard is part of the **Maatify Ecosystem**, providing:

- Unified brute-force protection
- Distributed blocking logic
- Real and Fake driver symmetry
- Full audit & monitoring pipeline (Planned)

Perfect for **production security** and **deterministic security testing**.
<!-- EXECUTOR_OVERVIEW_END -->

---

## 📘 Table of Contents
- [Features](#-features)
- [Core Concepts](#-core-concepts)
- [Installation](#-installation)
- [Usage](#-usage)
- [Drivers](#-drivers)
- [Audit System](#-audit-system)
- [Monitoring](#-monitoring)
- [Testing](#-testing)
- [Architecture](#-architecture-overview)
- [Roadmap & Status](#-roadmap--phase-status)
- [Phase Documentation](#-development-phases--documentation-links)
- [License](#-license)
- [Author](#-author)

---

## 🚀 Features
<!-- EXECUTOR_FEATURES_START -->
* Immutable security DTOs (LoginAttemptDTO, SecurityBlockDTO)
* Permanent & temporary block model
* Unified driver contract (SecurityGuardDriverInterface)
* Real vs Fake execution symmetry at contract level
* Deterministic adapter-driven architecture
* Fake-ready security modeling via maatify/data-fakes
* Production + CI-safe contract behavior
* (Planned — Phase 3) MySQL / Redis / MongoDB drivers
* (Planned — Phase 6) Full audit event pipeline
* (Planned — Phase 10–14) Logger, Monitoring, Webhooks & Alerts
<!-- EXECUTOR_FEATURES_END -->

---

## 🧩 Core Concepts
<!-- EXECUTOR_CORE_START -->
- **Attempt Handling** → all logins and requests go through one engine  
- **Drivers** → security state is stored via adapters only  
- **Resolvers** → switch between real and fake drivers  
- **Blocks** → temporary or permanent blocking  
- **DTO Immutability** → all security data structures are immutable  
- **Permanent Blocks** → manual blocks may have no expiration  
- **Audits** → every security event will be tracked starting Phase 6
- **Symmetry Guarantee** → fake and real drivers behave identically  
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

<!-- EXECUTOR_USAGE_START -->

⚠️ Usage examples will be injected automatically after Phase 4 when `SecurityGuardService` is finalized.

<!-- EXECUTOR_USAGE_END -->

---

## 🧱 Drivers

<!-- EXECUTOR_DRIVERS_START -->

(Planned — Phase 3)

* MySQL Driver
* Redis Driver
* MongoDB Driver

All drivers will operate **ONLY** through `maatify/data-adapters`.  
Direct PDO / Doctrine DBAL / Redis Extension / Predis / MongoDB clients are forbidden.

<!-- EXECUTOR_DRIVERS_END -->

---

## 🗂 Audit System

<!-- EXECUTOR_AUDIT_START -->

(Planned — Phase 6)

Audit system will introduce:

* Unified `AuditEventDTO`
* Mongo audit forwarding
* TTL-based cleanup
* Paginated audit history

<!-- EXECUTOR_AUDIT_END -->

---

## 📡 Monitoring

<!-- EXECUTOR_MONITORING_START -->

(Planned — Phase 14)

Monitoring APIs will include:

* Health endpoint
* Statistics endpoint
* Manual unblock

<!-- EXECUTOR_MONITORING_END -->

---

## 🧪 Testing

<!-- EXECUTOR_TESTING_START -->

All tests are executed using:

* `maatify/data-fakes` for deterministic fake testing
* `maatify/data-adapters` for real driver integration tests
* Full behavior parity is mandatory

<!-- EXECUTOR_TESTING_END -->

---

## 🏗 Architecture Overview

<!-- EXECUTOR_ARCH_START -->

Layered Architecture:

Application  
→ SecurityGuardService (Planned — Phase 4)  
→ SecurityGuard Drivers (Planned — Phase 3)  
→ AdapterInterface  
→ maatify/data-adapters (Real) | maatify/data-fakes (Fake)

<!-- EXECUTOR_ARCH_END -->

---

## 📅 Roadmap & Phase Status

<!-- EXECUTOR_PHASE_TABLE_START -->

(Executor auto-loads from roadmap.json)

✅ Current stable phase: **Phase 2 (Core Architecture & DTOs)**  
▶️ Next active phase: **Phase 3 (Driver Implementations)**

<!-- EXECUTOR_PHASE_TABLE_END -->

---

## 📚 Development Phases & Documentation Links

<!-- EXECUTOR_PHASE_INDEX_START -->

### ✅ Phase 1 — Environment Setup (Completed)
- 📄 Documentation: [`docs/phases/README.phase1.md`](phases/README.phase1.md)
- ✅ Status: Completed
- 🗓 Date: 2025-12-08
- 🧱 Delivered:
    - Project bootstrap and repository initialization
    - Composer configuration (`composer.json`)
    - Environment template (`.env.example`)
    - PHPUnit setup (`phpunit.xml.dist`)
    - Test bootstrap (`tests/bootstrap.php`)
    - CI preparation
    - PSR-4 namespace autoloading

### ✅ Phase 2 — Core Architecture & DTOs (Completed)
- 📄 Documentation: [`docs/phases/README.phase2.md`](phases/README.phase2.md)
- ✅ Status: Completed
- 🗓 Date: 2025-12-08
- 🧱 Delivered:
    - Immutable DTOs:
        - `LoginAttemptDTO`
        - `SecurityBlockDTO`
        - `BlockTypeEnum`
    - Unified Driver Contract:
        - `SecurityGuardDriverInterface`
    - 100% test coverage for all DTOs & contracts

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
