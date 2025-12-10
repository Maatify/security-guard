# Phase 4 — Unified Event System Integration

**Status:** Completed
**Version:** 1.0.3
**Date:** 2025-12-10

## Overview
Phase 4 introduces a comprehensive Security Event System designed to decouple the core security logic from external logging, alerting, and monitoring systems. By implementing a unified event DTO and dispatching mechanism, `maatify/security-guard` can now broadcast security events (like login failures, blocks, and cleanups) to any PSR-compatible listener or logger.

## Key Deliverables

### 1. Unified Event DTO
- **`SecurityEventDTO`**: A normalized, immutable event envelope containing standard fields:
  - `eventId`: UUIDv7 for time-sortable unique IDs.
  - `action`: Standardized action type (e.g., `login_attempt`, `block_created`).
  - `platform`: Origin source (e.g., `web`, `api`, `cli`).
  - `timestamp`: UNIX timestamp.
  - `context`: Structured metadata.

### 2. Event Factory & Enums
- **`SecurityEventFactory`**: Centralizes event creation logic to ensure consistency.
- **`SecurityAction` & `SecurityPlatform`**: Extensible wrappers supporting both built-in enums (`SecurityActionEnum`, `SecurityPlatformEnum`) and custom string values.
- **`SecurityEventTypeEnum`**: Defines core event types.

### 3. Dispatcher System
- **`EventDispatcherInterface`**: Contract for event dispatching.
- **`SyncDispatcher`**: Basic synchronous dispatcher supporting closures and object listeners.
- **`PsrLoggerDispatcher`**: Out-of-the-box integration to log all security events via PSR-3 loggers.
- **`NullDispatcher`**: No-op implementation for testing or disabled events.

### 4. Service Integration
- **`SecurityGuardService`**: Updated to automatically emit events during key operations:
  - `recordFailure` -> emits `login_attempt`
  - `block` -> emits `block_created`
  - `unblock` -> emits `block_removed`
  - `cleanup` -> emits `cleanup`

## Testing Coverage
A complete PHPUnit test suite (`tests/Phase4/`) was implemented achieving **100% coverage** for the new components:
- **Unit Tests**:
  - Validated DTO serialization and factory logic.
  - Verified Enum integrity and wrapping classes.
  - Tested Dispatcher behavior (exception swallowing in SyncDispatcher, correct logging in PsrLoggerDispatcher).
- **Integration Tests**:
  - Verified event emission order and content using `FakeAdapter`.
- **Behaviour Tests**:
  - Simulated full security flows (Login Failed, Manual Block, Cleanup) to ensure events are fired correctly in real-world scenarios.

## Changes
- **Added**:
  - `src/DTO/SecurityEventDTO.php`
  - `src/Event/*` (Factory, Dispatchers, Contracts)
  - `src/Enums/*` (Action, Platform, EventType)
  - `tests/Phase4/*` (Comprehensive Test Suite)
- **Updated**:
  - `src/Service/SecurityGuardService.php` (Event emission logic)
  - `src/DTO/LoginAttemptDTO.php` (Helper `toEvent`)
  - `src/DTO/SecurityBlockDTO.php` (Helper `toCreatedEvent`, `toRemovedEvent`)

## Next Steps
- Proceed to **Phase 5** (Extended Driver Features or Advanced Rate Limiting).
