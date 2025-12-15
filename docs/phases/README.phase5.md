# 📄 **Phase 5: High-Level Security Logic & Auto-Blocking Engine**

**Status:** Completed
**Version:** 1.0.0
**Date:** 2025-12-12

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

---

# 📌 Summary

Phase 5 introduces the **High-Level Security Engine Layer**—a behavioral layer that sits above drivers and events.
This phase delivers the first *real* login-flow logic inside the Security Guard:

* Automatic blocking after repeated failures
* Automatic attempt reset on success
* Reporting existing blocks
* Dispatching normalized security events
* Runtime-configurable behavior via SecurityConfig

This elevates the library from a low-level driver toolkit into a **full security decision engine** ready to plug directly into login systems.

---

# 🚀 What’s New in Phase 5?

## 1️⃣ **SecurityConfig Integration**

A new configuration layer:

* Provides sane production defaults
* Can be overridden using:

  ```php
  $guard->setConfig($customConfig);
  ```
* Used internally to determine:

    * `maxFailures`
    * `blockSeconds`
    * behavior thresholds

---

## 2️⃣ **handleAttempt() — Full Login Attempt Lifecycle**

This is the core of Phase 5.

### Behavior:

#### ✔ If already blocked

Return **remaining block seconds**.

#### ✔ If login success

Reset counters and return `null`.

#### ✔ If login failure

Increment failure count and return updated count.

#### ✔ If threshold reached

Create an **AUTO block** and emit a blockCreated event.

### This replaces dozens of manual checks that applications used to write themselves.

---

## 3️⃣ **AUTO Blocking Support**

Phase 5 introduces:

```
BlockTypeEnum::AUTO
```

Used to distinguish:

* Manual admin block
* System-detected block

---

## 4️⃣ **High-Level Event Routing**

Phase 5 adds:

### `handleEvent(SecurityEventDTO $event)`

A generic router that forwards events directly to the dispatcher.

The logic layer now emits events for:

* login attempts
* failures
* auto blocks
* manual blocks
* unblocks
* cleanup

---

## 5️⃣ **SecurityGuardService Enhancements**

Several new capabilities:

### ✔ `$config` property

Stores active runtime configuration.

### ✔ `setConfig()`

Allows runtime overrides.

### ✔ Event emission consolidated

All high-level actions automatically create `SecurityEventDTO` objects and dispatch them.

### ✔ Auto-blocking logic integrated

The service can now independently decide when to block a subject.

---

# 🧠 Architecture Impact

Phase 5 officially transforms the library into:

### **A decision-making engine**, not just storage logic.

The new architecture layers:

```
+----------------------------------------+
|     High-Level Security Logic (Phase5) |
|   handleAttempt(), auto-block, routing |
+----------------------+-----------------+
                       |
   emits               |
+----------------------v-----------------+
|         Unified Event Architecture     |
|       (DTOs + Factory + Dispatchers)   |
+----------------------+-----------------+
                       |
                       v
                Drivers (Phase1–3)
```

The system now makes real decisions internally without requiring custom logic from consumers.

---

# 🧪 Tests (Planned / Ongoing)

Phase 5 requires test coverage for:

* Auto-block logic
* Threshold decision behavior
* handleAttempt flow
* Event emission verification
* Config override behavior

Tests will be added under:

```
tests/Phase5/
```

---

# 📦 Delivered Files & Modules

### ✔ Updated

* `SecurityGuardService.php`

### ✔ Added

* AUTO block logic
* High-level decision flow
* handleAttempt()
* handleEvent()

---

# 🎯 Phase 5 Outcome

At the end of Phase 5, Security Guard becomes:

* Capable of **directly powering login systems**
* Aware of context and thresholds
* Able to auto-block malicious subjects
* Able to reset behavior on success
* Fully event-driven at every decision point
* Ready for analytics and suspicious-activity layers

This phase establishes the essential decision-making intelligence that later phases (6 and beyond) will analyze, visualize, and optimize.

---

## 🔐 Phase 5.5 — IntegrationV2 Stabilization (Addendum)

This sub-phase introduced the final authoritative IntegrationV2 layer.

Key clarifications:
- IntegrationV2 tests are the only source of truth for real infrastructure behavior.
- Legacy integration tests are deprecated and excluded from PHPUnit execution.
- Unit and Coverage tests remain unchanged.
- All IntegrationV2 tests must:
  - Use DatabaseResolver
  - Load environment via EnvironmentLoader
  - Fail explicitly when infrastructure is unavailable
  - Avoid mocks, fakes, and hardcoded hosts

No production code was modified as part of this sub-phase,
except for bug fixes discovered by real integration tests.

---

# 🔜 Next Phase

## **Phase 6: Security Analytics & Real-Time Behavior Insights**

Planned goals:

* Suspicious activity scoring
* Pattern detectors
* Anomaly detection hooks
* Aggregated stats & incident summaries
* Real-time monitoring endpoints

---
