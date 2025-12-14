Mock Audit Report — maatify/security-guard
Summary
Total tests scanned: ~50 files
Total mocks/fakes found: Significant usage across all test layers.
Risk Assessment:
High Risk: Legacy integration tests (tests/Integration/Redis, tests/Drivers/Support) relying on hardcoded 127.0.0.1 or anonymous adapters that deviate from production AdapterResolver logic.
Medium Risk: Behaviour tests (tests/Phase4/Behaviour) simulating full flows using FakeAdapter, creating a false sense of security regarding persistence and race conditions.
Low Risk: Pure unit tests (tests/Unit, tests/DTO) using mocks for isolation.
Detailed Findings
tests/Integration/Redis/AbstractRedisTestCase.php & subclasses
File: tests/Integration/Redis/PredisIntegrationTest.php, PhpRedisIntegrationTest.php
Mocked Component: AdapterInterface (Anonymous implementation)
Reason: To manually wire \Redis or Predis\Client without using the production Resolver.
Simulated Behavior: Manual connection handling, bypassing EnvironmentConfig and DatabaseResolver.
Risk Level: HIGH
IntegrationV2 Mapping: Redis Integration
Action Required: Replace by IntegrationV2
tests/Drivers/Support/RealRedisAdapter.php & Consumers
File: tests/Drivers/Support/RealRedisAdapter.php, tests/Drivers/Phase3G12RealRedisTest.php
Mocked Component: AdapterInterface (Concrete test implementation)
Reason: Historical helper for testing Redis drivers before data-adapters was standardized.
Simulated Behavior: Connects to 127.0.0.1:6379 ignoring environment variables.
Risk Level: HIGH
IntegrationV2 Mapping: Redis Integration
Action Required: Replace by IntegrationV2
tests/Drivers/RedisSecurityGuardTest.php
File: tests/Drivers/RedisSecurityGuardTest.php
Mocked Component: FakeAdapter, FakeIdentifierStrategy
Reason: Unit testing the driver logic without a real database.
Simulated Behavior: In-memory key-value storage.
Risk Level: LOW
IntegrationV2 Mapping: Redis Integration (coverage exists in V2 via RedisIntegrationFlowTest)
Action Required: Keep as Unit (rename to Unit/DriverTest?) or Delete if redundant. Recommendation: Keep as Unit.
tests/Resolver/SecurityGuardResolverTest.php
File: tests/Resolver/SecurityGuardResolverTest.php
Mocked Component: Redis, PDO, MongoDB\Database, AdapterInterface
Reason: To verify the Resolver's logic (e.g. "if Redis class exists, return RedisDriver") without needing extensions installed.
Simulated Behavior: Existence of extensions and successful driver instantiation.
Risk Level: LOW
IntegrationV2 Mapping: N/A (Internal logic test)
Action Required: Keep as Unit
tests/Phase4/Behaviour/FullSecurityFlowTest.php
File: tests/Phase4/Behaviour/FullSecurityFlowTest.php
Mocked Component: FakeAdapter, FakeIdentifierStrategy
Reason: To verify the orchestration of the Service layer (events, blocking) without infrastructure.
Simulated Behavior: Full lifecycle (Login -> Block -> Event) in memory.
Risk Level: MEDIUM (Logic is tested, but persistence/TTL is fake).
IntegrationV2 Mapping: Redis Integration / MySQL Integration
Action Required: Duplicate (Unit + IntegrationV2). The fake version tests logic speed; V2 tests reality.
tests/Coverage/SecurityGuardServiceTest.php
File: tests/Coverage/SecurityGuardServiceTest.php
Mocked Component: FakePredisClient, Anonymous AdapterInterface
Reason: High-coverage unit testing of the Service layer.
Simulated Behavior: Service interaction with a controlled adapter.
Risk Level: LOW
IntegrationV2 Mapping: N/A
Action Required: Keep as Unit
tests/Fake/* (Utility Classes)
File: FakeAdapter, FakeIdentifierStrategy, FakeSecurityGuardDriver
Mocked Component: Entire infrastructure layer.
Reason: Utilities for Unit tests.
Simulated Behavior: In-memory storage.
Risk Level: LOW (as long as they are only used for Unit tests).
IntegrationV2 Mapping: N/A
Action Required: Keep as Unit (Test Utilities).
Final Recommendations
tests/Integration/Redis/*: Replace by IntegrationV2
tests/Drivers/Support/RealRedisAdapter.php: Replace by IntegrationV2
tests/Drivers/Phase3G12RealRedis.php:* Replace by IntegrationV2
tests/Drivers/RedisSecurityGuardTest.php: Keep as Unit
tests/Resolver/SecurityGuardResolverTest.php: Keep as Unit
tests/Phase4/Behaviour/*: Duplicate (Unit + IntegrationV2)
tests/Coverage/*: Keep as Unit