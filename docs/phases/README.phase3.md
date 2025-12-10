# Phase 3 — Driver Implementations  

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

**Version:** 1.0.0
**Date:** 2025-12-09  
**Status:** ✔ Completed  
**Package:** `maatify/security-guard`    
**Scope:** Implement all storage drivers (Redis, MySQL PDO, MySQL DBAL, MongoDB) with unified semantics and strict adapter usage.

---

## 🎯 Overview

Phase 3 completes the **storage layer foundation** of the Security Guard engine.  
All drivers now implement **strict, deterministic and cross-backend behavior** using `maatify/data-adapters`, with **no raw client usage allowed**.

This phase finalizes:

- Fully functional Redis, MySQL (PDO/DBAL), and MongoDB drivers  
- Shared driver logic extracted into `AbstractSecurityGuardDriver`  
- A unified resolver that automatically selects the correct driver  
- Strict DTO formats for login attempts & blocks  
- Normalized identifier strategy support  
- PHPStan-clean implementations with safe casting, typed arrays, and storage-safe payloads

---

## 🧱 Core Objectives

| Objective                              | Result                                                    |
|----------------------------------------|-----------------------------------------------------------|
| Implement all driver backends          | ✔ Redis, MySQL (PDO/DBAL), MongoDB                        |
| Unify behavior across all drivers      | ✔ Shared abstract driver + normalizers                    |
| Remove all direct raw-client logic     | ✔ All access via adapter drivers                          |
| Deterministic block encoding/decoding  | ✔ Shared encodeBlock/decodeBlock                          |
| Flexible identity hashing              | ✔ IdentifierStrategyInterface + DefaultIdentifierStrategy |
| Automatic driver resolution            | ✔ SecurityGuardResolver                                   |
| Guarantee PHPStan level-max compliance | ✔ Fully cleaned in this phase                             |

---

## 📦 Delivered Components

### 🧩 **Shared Foundation**
- `AbstractSecurityGuardDriver`  
  - Deterministic identifier construction  
  - Normalized IP/subject  
  - Shared time helpers  
  - Block payload encoder/decoder  
  - Unified public API delegating to `do*()` storage methods  

---

### 🔐 **Redis Driver**

`src/Drivers/RedisSecurityGuard.php`

Capabilities:
- Atomic failure counting via `INCR`
- Auto-expiring attempt window using `resetAfter`
- Block storage via Redis hashes (`hMSet`, `hGetAll`)
- TTL-managed block expiration
- Uses `RedisClientProxy` to normalize Predis / phpredis

Support File:
- `src/Drivers/Support/RedisClientProxy.php`

---

### 🗄 **MySQL Drivers**

#### 1. **PDO implementation**
`src/Drivers/MySQL/PdoMySQLDriver.php`

- Strict prepared statements  
- `REPLACE INTO` for block writes  
- Typed int timestamps  
- Safe COUNT queries  
- Fully SQL-encapsulated

#### 2. **DBAL implementation**
`src/Drivers/MySQL/DbalMySQLDriver.php`

- `fetchAssociative`, `fetchOne`  
- Strict casting  
- DOCTRINE-compatible REPLACE  
- Centralized SQL exactly mirrors PDO behavior  

#### 3. **Unified MySQL wrapper**
`src/Drivers/MySQL/MySQLSecurityGuard.php`

Detects a driver type:

| Detected                   | Used Driver       |
|----------------------------|-------------------|
| `PDO`                      | `PdoMySQLDriver`  |
| `Doctrine\DBAL\Connection` | `DbalMySQLDriver` |

---

### 🍃 **MongoDB Driver**
`src/Drivers/Mongo/MongoSecurityGuard.php`

Features:
- TTL indexes for attempts cleanup
- Expiring and permanent block lookup
- Predictable document schema
- `insertOne`, `updateOne`, isolated aggregates
- Fully typed payload decoding

---

### 🧭 **Resolver**
`src/Resolver/SecurityGuardResolver.php`

Automatically resolves:

| Driver           | Backend            |
|------------------|--------------------|
| Redis / Predis   | RedisSecurityGuard |
| PDO              | MySQLSecurityGuard |
| Doctrine DBAL    | MySQLSecurityGuard |
| MongoDB\Database | MongoSecurityGuard |

---

### 🎛 **Service Layer**
`src/Service/SecurityGuardService.php`

High-level Facade:

- `recordFailure()`  
- `resetAttempts()`  
- `block()` / `unblock()`  
- `isBlocked()`  
- `getRemainingBlockSeconds()`  
- `getStats()`  

---

### 🧬 **Identifier System**

- `IdentifierStrategyInterface`
- `DefaultIdentifierStrategy`
- `IdentifierModeEnum`

Modes:

| Mode              | Behavior     |
|-------------------|--------------|
| IDENTIFIER_ONLY   | subject only |
| IP_ONLY           | IP only      |
| IDENTIFIER_AND_IP | combined     |

Hashed using SHA-256 with a configurable prefix.

---

## 📑 Schema Added

`resources/sql/security_guard_schema.sql`  
- `sg_attempts`  
- `sg_blocks`  
- Indexed, timestamp-based, compatible with PDO & DBAL

---

## 🧪 Tests Added (Partial – coverage continues in Phase 4)

- Enum coverage  
- Config DTO / Loader coverage  
- Abstract driver tests  
- Redis/MySQL block encoding and behavior tests  
- Fake drivers (for phase4 expansion)  

Files under:

```

tests/Config/
tests/Drivers/
tests/Fake/
tests/Identifier/

```

---

## 🔧 Architectural Guarantees

This phase ensures:

- Every storage backend now behaves **identically**  
- All block/attempt formats are **normalized**  
- No backend can break semantics  
- All raw driver differences are abstracted away  
- System now ready for Phase 4: **Core Logic & Advanced Behaviors**  

---

## 📌 Phase Outputs Summary

### **Created**
```

src/Drivers/*
src/Identifier/*
src/Resolver/SecurityGuardResolver.php
src/Service/SecurityGuardService.php
resources/sql/security_guard_schema.sql
tests/*

```

### **Updated**
```

roadmap.json — phase2+phase3 marked completed
api-map.json — full driver API introspection added
DTOs normalized (LoginAttemptDTO, SecurityBlockDTO)

```

---

## 🚀 Next Phase (Phase 4 Preview)

- Backoff logic integration  
- Suspicious activity heuristics  
- Adaptive blocking  
- Event dispatching hooks  
- 100% test coverage  

--- 
Status: Phase 3 successfully completed.