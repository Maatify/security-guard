# Phase 5 – Unified Coverage Blueprint

All coverage MUST focus on public API behaviours only.

---

## Target Classes

### SecurityGuardService
- handleAttempt()
- block()
- unblock()
- resetAttempts()
- isBlocked()
- getRemainingBlockSeconds()
- cleanup()
- getStats()
- setConfig()
- getConfig()

### SecurityConfig
- windowSeconds()
- blockSeconds()
- maxFailures()
- computeBackoffSeconds()
- identifierMode()
- keyPrefix()
- backoffEnabled()

### DefaultIdentifierStrategy
- makeId()

---

## Coverage Areas

### Functional
- success/failure logic
- threshold detection
- block/unblock lifecycle
- event dispatching

### Behavioural
- multi-flow
- TTL expiry
- backoff escalation
- manual blocks

### Stress
- race conditions
- distributed flows
- clock drift

### Fault Injection
- corrupted counter
- missing TTL
- transient driver error
- unpredictable recovery sequences

### Analytics
- state aggregation
- heavy-load correctness

---

## Required Coverage Matrix

| Feature               | Required Tests |
|-----------------------|----------------|
| Success/Failure Logic | ✔              |
| Auto Block            | ✔              |
| Manual Block          | ✔              |
| Backoff               | ✔              |
| Expiry & Drift        | ✔              |
| Multi-Flow Isolation  | ✔              |
| Policy Switching      | ✔              |
| Distributed Attacks   | ✔              |
| Race Conditions       | ✔              |
| Fault Injection       | ✔              |
| Identity Mutation     | ✔              |
| Event Ordering        | ✔              |
| Analytics             | ✔              |

