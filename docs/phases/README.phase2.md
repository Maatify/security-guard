# Phase 2: Core Architecture & DTOs

## Status: Completed
- **Version:** 1.0.1
- **Date:** 2025-12-08

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

---

## Summary

Finalized the internal core architecture and immutable security DTOs for `maatify/security-guard`.  
This phase establishes the **data contracts and driver interface** that all future real and fake drivers will follow.

Delivered components are now fully:
- Immutable
- Testable
- Fake/Real execution agnostic
- Production-ready at the contract level

---

## Changes

### ✅ Added

#### 📦 DTOs

- `Maatify\SecurityGuard\DTO\LoginAttemptDTO`
    - Immutable DTO for login attempts
    - Defensive validation
    - Static factory method: `now()`
    - Context payload support
    - JSON serialization via `JsonSerializable`

- `Maatify\SecurityGuard\DTO\SecurityBlockDTO`
    - Immutable DTO for security blocks
    - Supports **temporary and permanent blocks** (`expiresAt = null`)
    - Helper methods:
        - `getRemainingSeconds()`
        - `isExpired()`
    - JSON serialization with:
        - Remaining time
        - Expiration state

- `Maatify\SecurityGuard\DTO\BlockTypeEnum`
    - `AUTO`
    - `MANUAL`
    - `SYSTEM`

---

#### 🔌 Driver Contract

- `Maatify\SecurityGuard\Contracts\SecurityGuardDriverInterface`  
  Finalized unified contract for all storage drivers:

    - `recordFailure(): int`
    - `resetAttempts()`
    - `getActiveBlock()`
    - `isBlocked()`
    - `getRemainingBlockSeconds(): ?int`
    - `block()`
    - `unblock()`
    - `cleanup()`
    - `getStats(): array`

✅ Contract Guarantees:
- No direct database clients allowed
- Unified behavior across all drivers
- Fully fake-testable
- Monitoring & cleanup ready

---

### 🔄 Updated

- `api-map.json`
    - Added `BlockTypeEnum`
    - Updated DTO method signatures
    - Replaced:
        - `getBlockDetails` → `getActiveBlock`
    - Added:
        - `cleanup`
        - `getStats`
        - `getRemainingBlockSeconds`

---

## ✅ Tests

Added full test coverage for all Phase 2 components:

- `tests/DTO/LoginAttemptDTOTest.php`
- `tests/DTO/SecurityBlockDTOTest.php`
- `tests/DTO/BlockTypeEnumTest.php`
- `tests/Contracts/SecurityGuardDriverInterfaceTest.php`

✅ **Coverage:** 100% for all Phase 2 DTOs & Contracts

---

## ✅ Phase 2 Outcome

At the end of this phase, the project now has:

- A complete **immutable DTO layer**
- A fully defined **driver contract**
- A standardized **block & attempt model**
- A stable base for:
    - Phase 3 (Driver Implementations)
    - Phase 4 (SecurityGuardService)
    - Phase 14+ (Monitoring & APIs)

---

## 🔜 Next Phase

### Phase 3: Driver Implementations
- MySQL Driver
- Redis Driver
- MongoDB Driver  
  (All via `maatify/data-adapters`, no direct DB clients)

---
