# Changelog — maatify/security-guard

All notable changes to this project will be documented in this file.

This project follows:

- **Semantic Versioning (SemVer)**: `MAJOR.MINOR.PATCH`
- **Keep a Changelog** format: https://keepachangelog.com
- **Strict architectural rules** of the Maatify ecosystem

---

## [Unreleased]

### Planned
- Finalize audit history APIs
- Complete monitoring & admin control APIs
- Telegram alerts & webhook dispatcher
- Stress testing & coverage hardening
- First stable public Packagist release

---

## [1.0.0] — 2025-12-XX

🎉 **First public stable release of `maatify/security-guard`**

This release introduces a fully decoupled, multi-driver security protection engine designed to defend PHP systems against brute force, abuse, and suspicious activity with real-time blocking and audit tracing.

---

### ✅ Added

#### 🧱 Core Architecture
- Security Guard core service layer (`SecurityGuardService`)
- Unified driver contract based on `AdapterInterface`
- Strict resolver for **real vs fake execution**
- Environment-based threshold configuration

#### 📦 DTOs
- `LoginAttemptDTO`
- `SecurityBlockDTO`

#### 🔌 Storage Drivers (via `maatify/data-adapters`)
- MySQL Security Guard Driver
- Redis Security Guard Driver
- MongoDB Security Guard Driver

✅ All drivers:
- Use TTL-based expiration
- Are fully adapter-driven
- Are forbidden from direct DB client access

---

### 🔁 Rate Limiter Integration (Phase 5)

- Optional bridge to `maatify/rate-limiter`
- Event-driven forwarding without introducing DB coupling
- Flood testing & integration hooks

---

### 🧪 Testing & Quality

- Deterministic **Fake Adapter tests** via `maatify/data-fakes`
- Real **Integration tests** via `maatify/data-adapters`
- PHPStan **Level MAX**
- PHPUnit full test suite
- Enforced CI with:
    - Tests
    - Static analysis
    - Coverage enforcement

---

### 🔒 Security

- Deterministic, bounded blocking logic
- Distributed-safe IP blocking
- Automatic TTL expiration for all critical records
- Immutable security DTOs
- Framework-agnostic architecture
- Full audit-forwarding pipeline (MongoDB-ready)

---

### 📚 Documentation

- `README.md`
- `CONTRIBUTING.md`
- `SECURITY.md`
- `CODE_OF_CONDUCT.md`
- Phase-based documentation system

---

### 🧠 Architectural Guarantees

- ✅ No direct PDO, Redis, or MongoDB client usage
- ✅ All real execution goes through `maatify/data-adapters`
- ✅ All fake execution & adapter behavior tests go through `maatify/data-fakes`
- ✅ Fully decoupled, testable, and framework-agnostic

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

### [1.1.0] — Planned

- Full audit history API
- Advanced audit filtering & indexing
- PSR Logger integration
- Telegram alert service
- Webhook dispatcher & retry engine

---

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
