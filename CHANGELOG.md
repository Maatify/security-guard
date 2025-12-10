# ✅ Changelog — `maatify/security-guard`

All notable changes to this project will be documented in this file.

This project follows:

* **Semantic Versioning (SemVer)** — `MAJOR.MINOR.PATCH`
* **Keep a Changelog** — https://keepachangelog.com
* **Strict Maatify Ecosystem Architecture**

---

## 🌐 GitHub Releases

| Version        | Release Notes          | Compare                                                                                |
|----------------|------------------------|----------------------------------------------------------------------------------------|
| **Unreleased** | —                      | [`v1.0.0...HEAD`](https://github.com/Maatify/security-guard/compare/v1.0.0...HEAD)     |
| **1.0.0**      | Initial stable release | [`v0.0.0...v1.0.0`](https://github.com/Maatify/security-guard/compare/v0.0.0...v1.0.0) |

> 📝 GitHub automatically attaches source code archives (`Source Code (zip)` / `Source Code (tar.gz)`) to each release.  
> You only attach **PHAR** if we later build one (optional).

---

## [Unreleased]
(*No tag yet*)

### Planned

* Finalize audit history APIs  
* Monitoring & admin control APIs  
* Telegram alerts & webhook dispatcher  
* Stress testing and coverage increase  
* First public Packagist release pipeline  

---

# [1.0.0] — 2025-12-XX  
### 🎉 First Public Stable Release  
**Tag:** `v1.0.0`  
**Compare:** https://github.com/Maatify/security-guard/compare/v0.0.0...v1.0.0  
**Milestone:** https://github.com/Maatify/security-guard/milestone/1  

---

## 🚀 Release Highlights (GitHub Optimized)

- Complete multi-driver security engine  
- Fully immutable DTO architecture  
- Real-time security event factory  
- Three real drivers (MySQL, Redis, MongoDB)  
- Unified dispatcher pipeline  
- Full adapter-driven architecture  
- Zero direct DB/Redis/Mongo usage  
- PHPStan Max + full CI 

---

## 📦 Assets Included (Auto by GitHub)

GitHub generates downloadable source archives:

- **Source Code (zip)**  
- **Source Code (tar.gz)**  

No custom binary assets are available in this version.

---

## ⚙️ Upgrade Notes (for developers)

This is the **first major stable version**.  
No breaking changes from previous tags because no previous tags existed.

Developers implementing this release should:

- Use adapters only (`maatify/data-adapters`)  
- Ignore legacy direct DB calls (not allowed)  
- Register event dispatcher via `$service->setEventDispatcher()`  
- Use strict DTO constructors  

---

## 🧱 Added

### Core Architecture (Phase 2)

* Immutable DTO set
  - `LoginAttemptDTO`
  - `SecurityBlockDTO`
  - `SecurityEventDTO`
* Defensive validation
* Shared identifier strategy
* Storage-agnostic behavior

---

### Enums (Phase 2 + Phase 4)

* `BlockTypeEnum`
* `SecurityActionEnum`
* `SecurityPlatformEnum`

**Extensible:**

* `SecurityAction`
* `SecurityPlatform`

---

### Driver Contract (Phase 2)

Defines:

* `recordFailure()`
* `resetAttempts()`
* `block()`
* `unblock()`
* `cleanup()`
* `getStats()`

Guaranteed:

* No direct PDO/Redis/Mongo  
* Only adapters allowed  

---

### Storage Drivers (Phase 3)

Adapter-implemented:

* MySQLSecurityGuardDriver  
* RedisSecurityGuardDriver  
* MongoSecurityGuardDriver  

All support:

* TTL expiration  
* Atomic operations  
* Deterministic behavior  

---

### Event System (Phase 4)

* `SecurityEventDTO`  
* `SecurityEventFactory`  
* Auto-event emission for all security actions  

---

### Event Dispatchers (Phase 4)

* `NullDispatcher`  
* `SyncDispatcher`  
* `PsrLoggerDispatcher`  

---

### Testing & Quality

* Full DTO & contract coverage  
* Fake adapter coverage  
* Real integration adapter coverage  
* PHPStan Level Max  
* CI for tests + analysis + coverage  

---

### Documentation

* README.md  
* docs/README.full.md  
* CONTRIBUTING.md  
* SECURITY.md  
* CODE_OF_CONDUCT.md  
* API Map  
* phase-output.json  

---

## ⚠️ Breaking Changes
None — initial stable version.

---

## 🐛 Fixed
N/A — new release.

---

## 🤝 Contributors
Maatify.dev Engineering Team

---

## 🔜 Upcoming Versions

### [1.1.0] — Planned
* Audit history API  
* Structured PSR-3 logging  
* Telegram alerts  
* Webhook dispatcher with retries  

---

### [1.2.0] — Planned
* Attack simulation framework  
* High-load Redis/Mongo benchmark suite  
* Adaptive multi-vector blocking  

---

### [2.0.0] — Future
* AI-based abuse detection  
* Reputation scoring  
* Geo-distributed enforcement coordinator  

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>