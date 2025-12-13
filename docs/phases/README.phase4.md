# 📄 **Phase 4: Unified Event Architecture & Dispatchers**

**Status:** Completed  
**Version:** 1.0.0  
**Date:** 2025-12-10  

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](../../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

---

# 📌 Summary

Phase 4 introduces the **Unified Event Architecture** for the `maatify/security-guard` engine.
This system provides a complete, extensible, immutable event model used for:

* Auditing
* Alerting (Telegram, email, admin notifications)
* Monitoring dashboards
* Logging (file, database, SIEM)
* Rate limiter integration
* User behavior analysis
* Future rule engines and anomaly detection

This phase also adds a **pluggable Dispatcher System**:
allowing consumers to subscribe to security events without modifying the engine or drivers.

The design follows:

* Immutable DTOs
* Extensible value objects
* Enum-based built-in actions/platforms
* Zero-dependency dispatchers
* PSR-friendly extensibility
* Deterministic event emission

Phase 4 completes the “top layer” of the engine that sits above drivers and integrates with external systems.

---

# 🚀 Changes

## ✅ Added (Major Features)

---

## 1. **SecurityEventDTO**

Immutable unified event container representing any security action.

**Path:** `src/DTO/SecurityEventDTO.php`

Includes:

* `eventId` (UUIDv7)
* `action` (SecurityAction)
* `platform` (SecurityPlatform)
* `timestamp`
* `ip`
* `subject`
* optional `userId`, `userType`
* array `context`

This DTO is the backbone of event logging, alerting, and analytics.

---

## 2. **SecurityAction & SecurityActionEnum**

### `SecurityActionEnum`

**Path:** `src/Enums/SecurityActionEnum.php`

Built-in action types:

* `LOGIN_ATTEMPT`
* `LOGIN_SUCCESS`
* `LOGIN_FAILURE`
* `BLOCK_CREATED`
* `BLOCK_REMOVED`

### `SecurityAction` (Extensible Value Object)

**Path:** `src/Event/SecurityAction.php`

Allows:

```php
SecurityAction::custom('password_reset');
```

This design ensures flexibility without breaking strict typing.

---

## 3. **SecurityPlatform & SecurityPlatformEnum**

### `SecurityPlatformEnum`

**Path:** `src/Enums/SecurityPlatformEnum.php`

Built-in platforms:

* `web`
* `api`
* `mobile`
* `admin`
* `cli`
* `system`

### `SecurityPlatform` (Value Object)

**Path:** `src/Event/SecurityPlatform.php`

Supports:

```php
SecurityPlatform::custom('partner_gateway');
```

---

## 4. **SecurityEventFactory**

**Path:** `src/Event/SecurityEventFactory.php`

Converts low-level DTOs into SecurityEventDTO:

* `fromLoginAttempt()`
* `blockCreated()`
* `blockRemoved()`
* `cleanup()`
* `custom()`

Includes a **UUIDv7 generator** for chronological sorting.

---

## 5. **EventDispatcherInterface**

**Path:** `src/Event/Contracts/EventDispatcherInterface.php`

Defines a unified dispatching contract:

```php
dispatch(SecurityEventDTO $event): void;
```

---

# 📡 Built-in Dispatchers

Phase 4 delivers three production-ready dispatchers.

---

## 6. **NullDispatcher**

**Path:** `src/Event/Dispatcher/NullDispatcher.php`
Does nothing — safe default.

Used when applications don't need event handling.

---

## 7. **SyncDispatcher**

**Path:** `src/Event/Dispatcher/SyncDispatcher.php`

* Executes multiple listeners synchronously
* Listeners receive `SecurityEventDTO`
* Errors are isolated and never break the chain
* Suitable for local logging, debugging, or inline alerting

Example:

```php
$dispatcher = new SyncDispatcher([
    fn(SecurityEventDTO $e) => error_log(json_encode($e)),
]);
```

---

## 8. **PsrLoggerDispatcher**

**Path:** `src/Event/Dispatcher/PsrLoggerDispatcher.php`

Integrates the event system with any PSR-3 logger (e.g., Monolog):

```php
$dispatcher = new PsrLoggerDispatcher($logger);
$guard->setEventDispatcher($dispatcher);
```

Logs `security_event` with full JSON context.

---

# 🔧 Updated Components

---

## 9. LoginAttemptDTO & SecurityBlockDTO — Event Helpers

Both DTOs now contain methods for event conversion:

### In `LoginAttemptDTO`

```php
toEvent(SecurityPlatform $platform, ?int $userId = null, ?string $userType = null)
```

### In `SecurityBlockDTO`

```php
toCreatedEvent()
toRemovedEvent()
```

These helpers simplify application-level integrations and reduce duplication.

---

## 10. **SecurityGuardService — Automatic Event Emission**

Events are automatically emitted from:

* `recordFailure`
* `block`
* `unblock`
* `cleanup`

Only if a dispatcher is provided:

```php
$guard->setEventDispatcher($dispatcher);
```

Otherwise, events are silently ignored (NullDispatcher pattern).

---

# 📊 Architecture Overview

The Phase 4 architecture sits on top of existing drivers:

```
               +---------------------------+
               |   SecurityGuardService    |
               +-------------+-------------+
                             |
                          emits
                             |
                 +-----------v-----------+
                 |   SecurityEventDTO    |
                 +-----------+-----------+
                             |
                       dispatched via
                             |
     +------------------+----------+---------------+
     |                  |          |               |
     |     Null         |   Sync   |   PSR Logger  |
     |   Dispatcher     | Dispatcher | Dispatcher   |
     +------------------+----------+---------------+
```

Drivers **do not know anything** about events.
Event architecture is fully optional and pluggable.

---

# 🧪 Tests

Tests for Phase 4 will be developed in a dedicated session.

Planned test suites include:

* `SecurityEventDTOTest`
* `SecurityEventFactoryTest`
* `SecurityActionTest`
* `SecurityPlatformTest`
* `NullDispatcherTest`
* `SyncDispatcherTest`
* `PsrLoggerDispatcherTest`
* `SecurityGuardServiceEventTest`

Coverage goal: **100% for Phase 4**

---

# 🎯 Phase 4 Outcome

At the end of Phase 4, the project now has:

* A complete, extensible Event Architecture
* Immutable event representation (SecurityEventDTO)
* Unified and flexible action & platform models
* Automatic event emission from SecurityGuardService
* Production-ready dispatchers (Null, Sync, PSR logger)
* Full compatibility with:

    * Monolog
    * Webhooks
    * Alerting systems
    * SIEM pipelines
* No BC-breaking changes
* Aligned with PSR principles

This phase elevates `maatify/security-guard` from a brute-force engine
to a fully observable **security event engine**.

---

## ⚠️ Clarification: Action Definition vs Event Emission

Defining an action in `SecurityActionEnum` does **not** imply that the action
is automatically emitted as a `SecurityEventDTO` in all phases.

For example:

- `LOGIN_SUCCESS` is a **valid built-in action**
- However, Phase 5 uses successful login attempts as an **internal state reset**
  and does **not emit a success event**

Event emission is **phase-driven and behavior-dependent**, not enum-driven.

Future phases (analytics, monitoring, SIEM) may choose to emit
`LOGIN_SUCCESS` events when they become behaviorally relevant.

---

# 🔜 Next Phase

## Phase 5: Real-Time Security Analytics & Monitoring API

Planned focus:

* Event stream abstraction
* Stats aggregation
* Suspicious activity detectors
* Integration with rate-limiter
* Dashboard endpoints

---

Status: Phase 4 successfully completed.