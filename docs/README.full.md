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
- Real + Fake driver symmetry
- Full audit & monitoring pipeline

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
* Multi-driver brute force protection
* MySQL / Redis / MongoDB unified drivers
* Distributed IP & identity blocking
* Adaptive thresholds via ENV
* Retry & unblock logic
* Full audit event pipeline
* PSR Logger support
* Webhook & Telegram alerts
* Fake driver simulation via maatify/data-fakes
* Production + CI-safe behavior
<!-- EXECUTOR_FEATURES_END -->

---

## 🧩 Core Concepts
<!-- EXECUTOR_CORE_START -->
- **Attempt Handling** → all logins & requests go through one engine  
- **Drivers** → security state is stored via adapters only  
- **Resolvers** → switch between real & fake drivers  
- **Blocks** → temporary or permanent blocking  
- **Audits** → every security event is tracked  
- **Symmetry Guarantee** → fake & real drivers behave identically  
<!-- EXECUTOR_CORE_END -->

---

## 📦 Installation
<!-- EXECUTOR_INSTALL_START -->
```bash
composer require maatify/security-guard
````

<!-- EXECUTOR_INSTALL_END -->

---

## 🛠 Usage

<!-- EXECUTOR_USAGE_START -->

(Executor will inject real usage examples here after phase 4)

<!-- EXECUTOR_USAGE_END -->

---

## 🧱 Drivers

<!-- EXECUTOR_DRIVERS_START -->

* MySQL Driver
* Redis Driver
* MongoDB Driver

All drivers operate **ONLY** through `maatify/data-adapters`.
Direct PDO / Redis / MongoDB clients are forbidden.

<!-- EXECUTOR_DRIVERS_END -->

---

## 🗂 Audit System

<!-- EXECUTOR_AUDIT_START -->

Audit system introduced starting from Phase 6:

* Unified `AuditEventDTO`
* Mongo audit forwarding
* TTL-based cleanup
* Paginated audit history

<!-- EXECUTOR_AUDIT_END -->

---

## 📡 Monitoring

<!-- EXECUTOR_MONITORING_START -->

Monitoring APIs introduced in Phase 14:

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
→ SecurityGuardService
→ SecurityGuard Drivers
→ AdapterInterface
→ Real or Fake Driver

<!-- EXECUTOR_ARCH_END -->

---

## 📅 Roadmap & Phase Status

<!-- EXECUTOR_PHASE_TABLE_START -->

(Executor auto-loads from roadmap.json)

<!-- EXECUTOR_PHASE_TABLE_END -->

---

## 📚 Development Phases & Documentation Links

<!-- EXECUTOR_PHASE_INDEX_START -->

(Executor auto-generates phase documentation links)

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