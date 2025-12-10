# Phase 3 – Test Group 4: Real TTL Expiration Validation

## Status: Completed ✅

### Description
Validated that block expirations respect the Time-To-Live (TTL) using both `sleep()` for real-time validation and Redis internal expiry mechanisms.

### Tests Implemented
- `testBlockExpiresAfterTTL_Fake`: Validates logical expiration using `sleep(3)`.
- `testBlockExpiresAfterTTL_Redis`: Validates integration expiration using Real Redis and `sleep(3)`.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`
- `Maatify\SecurityGuard\Drivers\RedisSecurityGuard`

---

# Phase 3 – Test Group 5: Multiple IPs Same Subject

## Status: Completed ✅

### Description
Ensured that when `IdentifierModeEnum::IDENTIFIER_AND_IP` is used, failure counts and blocks for the same Subject are isolated by IP address.

### Tests Implemented
- `testMultipleIPsSameSubjectIsolation`: Confirmed that blocks on `1.1.1.1` do not affect `2.2.2.2` for the same user.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`
- `Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy`

---

# Phase 3 – Test Group 6: Same IP Multiple Subjects

## Status: Completed ✅

### Description
Ensured that when `IdentifierModeEnum::IDENTIFIER_AND_IP` is used, failure counts and blocks for the same IP are isolated by Subject (User).

### Tests Implemented
- `testSameIPMultipleSubjectsIsolation`: Confirmed that blocks for `userA` do not affect `userB` on the same IP.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`
- `Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy`

---

# Phase 3 – Test Group 7: Identifier Collision Handling

## Status: Completed ✅

### Description
Validated behavior for explicit Identifier Modes:
- `IDENTIFIER_ONLY`: Different IPs collide (same bucket).
- `IP_ONLY`: Different Subjects collide (same bucket).

### Tests Implemented
- `testIdentifierCollisionBehavior`: Verified shared counters for `IDENTIFIER_ONLY` across IPs and `IP_ONLY` across Subjects.

### Coverage
- `Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy`

---

# Phase 3 – Test Group 8: Stats Accuracy Validation

## Status: Completed ✅

### Description
Validated that the `getStats()` method returns the correct structure and non-empty values after operations.

### Tests Implemented
- `testStatsAccuracyAfterOperations`: Checked for `failures` and `blocks` keys in stats array.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`

---

# Phase 3 – Test Group 9: Stats Under Load

## Status: Completed ✅

### Description
Validated driver stability and stats reporting under high repetition (loop of 150 operations).

### Tests Implemented
- `testStatsUnderHighLoad`: Executed 150 failure records and verified stats availability.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`

---

# Phase 3 – Test Group 10: Concurrency Simulation

## Status: Completed ✅

### Description
Simulated concurrent-like behavior by ensuring monotonic increase of failure counters across repeated calls, preventing lost updates in logical flow.

### Tests Implemented
- `testConcurrentFailureSimulation`: Verified that sequential calls return strictly increasing failure counts.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`

---

# Phase 3 – Test Group 11: Driver Without KeyValueAdapter

## Status: Completed ✅

### Description
Ensured robust error handling when a Driver is initialized with an incompatible Adapter (non-KeyValue).

### Tests Implemented
- `testDriverWithoutKeyValueAdapterThrows`: Verified that `LogicException` is thrown when using a generic Adapter instead of `KeyValueAdapterInterface`.

### Coverage
- `Maatify\SecurityGuard\Tests\Fake\FakeSecurityGuardDriver` (Safety Check)

---

# Phase 3 – Test Group 12: Real Redis Driver Integration

## Status: Completed ✅

### Description
Full integration testing with a real Redis instance (via `RealRedisAdapter`). Validated the full lifecycle of Record Failure -> Block -> Unblock -> Cleanup.

### Tests Implemented
- `testRedisIntegrationRecordFailure`: Verified atomic increments.
- `testRedisIntegrationBlock`: Verified block storage in Redis Hash.
- `testRedisIntegrationUnblock`: Verified block removal.
- `testRedisIntegrationCleanup`: Verified cleanup execution (safe no-op).

### Coverage
- `Maatify\SecurityGuard\Drivers\RedisSecurityGuard`
- `Maatify\SecurityGuard\Drivers\Support\RedisClientProxy`
