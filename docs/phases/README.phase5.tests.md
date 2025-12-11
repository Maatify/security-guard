# 🧪 Security Guard Phase 5 – Unified Test Architecture (STRICT)

This document defines the full behavioural test specification for the
Security Guard high-level orchestration engine.

It merges:

- Core functional tests
- Orchestration logic
- Failure / success transitions
- Blocking
- Backoff
- Multi-flow
- Event dispatching
- Fault injection
- Race conditions
- Distributed attacks
- Analytics correctness

No test interacts with underlying storage drivers directly.
All tests must use only the public API:

- handleAttempt()
- isBlocked()
- getRemainingBlockSeconds()
- block()
- unblock()
- resetAttempts()
- cleanup()
- getStats()
- setConfig()
- getConfig()

---

# 1. Login Attempt Behaviour

## Required Expectations
- Success resets failure counters.
- Failure increments counters.
- Threshold crossing triggers an automatic block.
- Block TTL is enforced correctly.
- `resetAfter` matches the active window.

## Scenarios
- fail → fail → success → fail (counter reset)
- fail × N → auto-block
- block → unblock → attempt

---

# 2. Policy Switching

## Required
`setConfig()` must instantly apply new rules without leftover state.

## Scenarios
- switch admin → customer → admin
- block under admin rules, switch policy, ensure behaviour remains consistent

---

# 3. Multi-Flow Isolation

## Required
Each (ip, subject) pair must maintain isolated counters.

## Scenarios
- A, B, C objects performing simultaneous attempts
- Shared IP with different subjects
- Shared subject with different IPs

---

# 4. Manual Blocking

## Required
Manual block overrides counters and backoff.

## Scenarios
- manual block then attempt
- manual block then unblock
- manual block + automatic block interaction

---

# 5. Event Dispatching

## Required
Event ordering:
1. login attempt
2. block created (if any)
3. block removed (if applicable)

## Scenarios
- attempt event fired
- block event fired
- cleanup event fired
- dispatcher collecting ordered events

---

# 6. Backoff Behaviour (Adaptive Lock)

## Required
Backoff increments using:
initialBackoffSeconds * multiplier^(n - threshold)

## Scenarios
- fail×threshold → blockSeconds
- fail×threshold+n → backoffSeconds
- backoff capped at maxBackoffSeconds
- success resets backoff state

---

# 7. Race Condition Robustness

## Required
State correctness even under simultaneous attempts.

## Scenarios
- two concurrent failures at t=0
- success/failure interleaving
- parallel flows on same subject from different IPs

---

# 8. TTL & Clock Drift

## Required
Engine must remain correct under expiry edge cases.

## Scenarios
- TTL expires exactly at boundary
- slight drift (±1s, ±5s)
- no negative remaining seconds

---

# 9. Fault Injection (Adapter Failure Simulation)

## Required
Engine recovers deterministically from adapter anomalies.

## Scenarios
- null counter from storage
- missing TTL update
- transient exception
- partial write failure

---

# 10. Distributed Attacks (Botnet Simulation)

## Required
Subject-level blocking dominates IP-level flows.

## Scenarios
- 50 IPs fail once → still block subject
- global attack vs focused attack
- high-frequency distributed loads

---

# 11. Identity Mutation & Replay Safety

## Required
Identity must remain stable and collision-resistant.

## Scenarios
- mutated username variants
- mutated device fingerprint
- replay context → same hashed identifier

---

# 12. Multi-Device Context Collisions

## Required
Context differences must not break grouping rules.

## Scenarios
- multiple devices, same username
- shared device, different usernames
- large structured context payload

---

# 13. Event Ordering Validation (Advanced)

## Required
Ordering must survive high load.

## Scenarios
- fail × threshold → auto-block → unblock
- 10,000 attempts ordered correctly

---

# 14. Analytics Integrity

## Required
`getStats()` must return consistent, correct aggregated values.

## Scenarios
- 10k attempts mixed
- multi-subject aggregation
- ranking stability  
