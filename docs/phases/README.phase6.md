# Phase 6: Same IP Multiple Subjects

## Status: Completed ✅

### Description
Ensured that when `IdentifierModeEnum::IDENTIFIER_AND_IP` is used, failure counts and blocks for the same IP are isolated by Subject (User).

### Tests Implemented
- `testSameIPMultipleSubjectsIsolation`: Confirmed that blocks for `userA` do not affect `userB` on the same IP.

### Coverage
- `Maatify\SecurityGuard\Drivers\AbstractSecurityGuardDriver`
- `Maatify\SecurityGuard\Identifier\DefaultIdentifierStrategy`
