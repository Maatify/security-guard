# Phase 4: Real TTL Expiration Validation

## Status: Completed ✅

### Description
Validated that block expirations respect the Time-To-Live (TTL) using both `sleep()` for real-time validation and Redis internal expiry mechanisms.

### Tests Implemented
- `testBlockExpiresAfterTTL_Fake`: Validates logical expiration using `sleep(3)`.
- `testBlockExpiresAfterTTL_Redis`: Validates integration expiration using Real Redis and `sleep(3)`.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`
- `Maatify\SecurityGuard\Drivers\RedisSecurityGuard`
