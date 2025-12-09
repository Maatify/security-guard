# Phase 5: Multiple IPs Same Subject

## Status: Completed ✅

### Description
Ensured that when `IdentifierModeEnum::IDENTIFIER_AND_IP` is used, failure counts and blocks for the same Subject are isolated by IP address.

### Tests Implemented
- `testMultipleIPsSameSubjectIsolation`: Confirmed that blocks on `1.1.1.1` do not affect `2.2.2.2` for the same user.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`
- `Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy`
