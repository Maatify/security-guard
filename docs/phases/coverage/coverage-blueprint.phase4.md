# Phase 4 Coverage Blueprint (100%)

### SecurityEventDTO
- constructor
- jsonSerialize

### SecurityEventFactory
- fromLoginAttempt
- blockCreated
- blockRemoved
- cleanup
- custom

### SecurityAction
- fromEnum
- custom

### SecurityPlatform
- fromEnum
- custom

### Dispatchers
NullDispatcher:
- dispatch executes with no side effects

SyncDispatcher:
- closure listener works
- object listener works
- exceptions inside listeners do NOT break flow

PsrLoggerDispatcher:
- logger->info called with serialized event

### Service Events
SecurityGuardService:
- recordFailure dispatches LOGIN_ATTEMPT event
- block dispatches BLOCK_CREATED event
- unblock dispatches BLOCK_REMOVED event
- cleanup dispatches CLEANUP event

### Behaviour Flows
- full flow (record → block → unblock → cleanup)
- login failed flow
- manual block flow
- cleanup flow
- sync dispatcher multi-listener flow
