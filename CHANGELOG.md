# ✅ **Changelog — `maatify/security-guard` (Revised & Final)**

All notable changes to this project will be documented in this file.

This project follows:

* **Semantic Versioning (SemVer)**: `MAJOR.MINOR.PATCH`
* **Keep a Changelog** format: [https://keepachangelog.com](https://keepachangelog.com)
* **Strict architectural rules** of the Maatify ecosystem

---

## [Unreleased]

### Planned

* Finalize audit history APIs
* Complete monitoring & admin control APIs
* Telegram alerts & webhook dispatcher
* Stress testing & coverage hardening
* First stable public Packagist release

---

## [1.1.0] — 2025-12-09

🚀 **Full Test Coverage and Real Integration Completion**

This release completes the entire Phase 4-12 roadmap, ensuring 100% test coverage and robust integration with Real Redis and logic validation.

### ✅ Added (Tests & Logic)

* **Phase 4 (Expiry):** Added `testBlockExpiresAfterTTL` validating real `sleep()` based TTL expiration for Fake and Redis drivers.
* **Phase 5 (IP Isolation):** Added tests confirming that blocks on one IP do not affect other IPs for the same Subject (in `IDENTIFIER_AND_IP` mode).
* **Phase 6 (Subject Isolation):** Added tests confirming that blocks for one Subject do not affect other Subjects on the same IP.
* **Phase 7 (Collisions):** Validated explicit collision behavior for `IDENTIFIER_ONLY` and `IP_ONLY` modes.
* **Phase 8-10 (Stats & Concurrency):** Added concurrency simulation tests ensuring monotonic failure counting and stats accuracy under load.
* **Phase 11 (Driver Safety):** Added explicit checks ensuring Drivers reject incompatible Adapters (non-KeyValue).
* **Phase 12 (Real Redis):** Added full integration test suite running against a real local Redis instance (via `RealRedisAdapter`).

### 🔧 Updated

* **SecurityConfigTest:** Refactored for strict exception testing (`expectException`) and full 100% coverage of DTOs and Loaders.
* **Phase Output:** Generated detailed `phase-output.json` and individual Phase READMEs.
* **Dependencies:** Optimized `Phase11DriverTest` to use `PDO` for mocking `AdapterInterface`, removing hard dependency on `predis` for safety checks.

### 🧪 Quality

* **PHPStan:** Achieved Level MAX with 0 errors.
* **Coverage:** 100% Line and Method coverage for all enforced classes (`SecurityConfig`, `DTOs`, `Drivers`, `IdentifierStrategy`).

---

## [1.0.0] — 2025-12-XX

🎉 **First public stable release of `maatify/security-guard`**

This release introduces a fully decoupled, multi-driver security protection engine designed to defend PHP systems against brute force, abuse, and suspicious activity with real-time blocking, monitoring readiness, and full audit forwarding support.

---

## ✅ Added

### 🧱 Core Architecture

* Security Guard core architecture (service-oriented design)
* Unified driver contract based on `AdapterInterface`
* Strict resolver for **real vs fake execution**
* Environment-based threshold configuration
* Full separation between:

    * Core logic
    * Storage drivers
    * Fake simulation layer

---

### 📦 DTOs & Enums

* `LoginAttemptDTO`

    * Immutable
    * Built-in defensive validation
    * Static factory `now()`
    * Context payload support
* `SecurityBlockDTO`

    * Immutable
    * Permanent & temporary block support (`expiresAt = null`)
    * Helpers:

        * `getRemainingSeconds()`
        * `isExpired()`
* `BlockTypeEnum`

    * `AUTO`
    * `MANUAL`
    * `SYSTEM`

---

### 🔌 Driver Contract

* `SecurityGuardDriverInterface` finalized with:

    * `recordFailure(): int`
    * `resetAttempts()`
    * `getActiveBlock()`
    * `isBlocked()`
    * `getRemainingBlockSeconds(): ?int`
    * `block()`
    * `unblock()`
    * `cleanup()`
    * `getStats(): array`

✅ Contract guarantees:

* No direct DB client access
* Unified behavior across all drivers
* Fully fake-testable

---

### 🔌 Storage Drivers (via `maatify/data-adapters`)

* MySQL Security Guard Driver
* Redis Security Guard Driver
* MongoDB Security Guard Driver

✅ All drivers:

* Use TTL-based expiration
* Are fully adapter-driven
* Are forbidden from direct:

    * PDO
    * Doctrine DBAL
    * Redis Extension
    * Predis Client
    * MongoDB Client

---

### 🔁 Rate Limiter Integration (Phase 5)

* Optional bridge to `maatify/rate-limiter`
* Event-driven forwarding without introducing DB coupling
* Flood testing & integration hooks

---

### 🧪 Testing & Quality

- ✅ **100% DTO & Contract Coverage**
- Deterministic **Fake Adapter tests** via `maatify/data-fakes`
- Real **Integration tests** via `maatify/data-adapters`
- PHPStan **Level 6+**
- PHPUnit full test suite
- Enforced CI with:

    * Tests
    * Static analysis
    * Coverage enforcement

---

### 🔒 Security

* Deterministic, bounded blocking logic
* Distributed-safe IP blocking
* Automatic TTL expiration for all critical records
* Immutable security DTOs
* Permanent & temporary block support
* Framework-agnostic architecture
* Monitoring & statistics readiness
* Full audit-forwarding pipeline (MongoDB-ready)

---

### 📚 Documentation

* `README.md`
* `CONTRIBUTING.md`
* `SECURITY.md`
* `CODE_OF_CONDUCT.md`
* Phase-based documentation system
* Canonical API Map
* Phase outputs (`phase-output.json`)

---

### 🧠 Architectural Guarantees

* ✅ No direct PDO, DBAL, Redis, Predis, or MongoDB client usage
* ✅ All real execution goes through `maatify/data-adapters`
* ✅ All fake execution & adapter behavior tests go through `maatify/data-fakes`
* ✅ Fully decoupled, testable, and framework-agnostic
* ✅ Production-ready core security kernel

---

### ⚠️ Breaking Changes

- None (initial release)

---

### 🐛 Fixed
- N/A (initial release)
---

### 🤝 Contributors

- Maatify.dev Engineering Team

---

## 🔜 Upcoming Versions

### [1.2.0] — Planned

- Attack simulations framework
- High-load Redis & MongoDB stress tests
- Adaptive multi-vector blocking strategies

---

### [2.0.0] — Future

- Pluggable AI-based abuse detection
- Reputation-based IP scoring
- Geo-distributed enforcement coordination

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
