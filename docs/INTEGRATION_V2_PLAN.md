# IntegrationV2 Plan — maatify/security-guard

## 🎯 Objective

Rebuild **real integration tests** for `maatify/security-guard` using the **actual production stack**:

* `maatify/data-adapters`
* `DatabaseResolver`
* `EnvironmentLoader`
* Real infrastructure (Redis / MySQL / Mongo)
* No mocks, no fakes, no hardcoded hosts

While **preserving existing Unit tests** for logic coverage and speed.

---

## 🧱 Core Principles (NON-NEGOTIABLE)

1. **Integration ≠ Unit**

    * Integration tests must reflect **real runtime behavior**
    * Unit tests may use fakes/mocks for logic isolation

2. **Resolver-First**

    * All integration tests MUST obtain adapters via:

      ```
      DatabaseResolver + EnvironmentConfig
      ```
    * No direct instantiation of adapters
    * No anonymous AdapterInterface implementations

3. **Environment-Driven**

    * No hardcoded `127.0.0.1`
    * No inline credentials
    * All configuration comes from `.env*` via `EnvironmentLoader`

4. **Fail Explicitly**

    * Integration tests **must fail** if infrastructure is unavailable
    * No silent `markTestSkipped()` for missing Redis/MySQL

5. **No Global Flush**

    * No `flushAll()`
    * No database-wide cleanup
    * Isolation via **keyPrefix / namespace only**

---

## 📂 Folder Structure

```
tests/
├── Unit/                    # Pure logic tests (fakes allowed)
├── Coverage/                # Coverage-driven unit tests
├── Integration/             # Legacy (to be deprecated)
└── IntegrationV2/           # ✅ New authoritative integration layer
    ├── BaseIntegrationV2TestCase.php
    ├── Redis/
    │   ├── RedisIntegrationFlowTest.php
    │   ├── RedisTTLExpiryTest.php
    ├── MySQL/               # (Planned)
    └── Mongo/               # (Planned)
```

---

## 🧪 Phase Breakdown (GATED)

### ✅ A1 — MOCK AUDIT (CLOSED)

**Goal**

* Identify all fake/mocked infrastructure usage
* Classify risk and replacement strategy

**Artifact**

* MOCK_AUDIT_REPORT.md

**Status**

* CLOSED ✅

---

### 🔄 A2 — REDIS INTEGRATION V2 (IN PROGRESS)

**Scope**

* RedisSecurityGuard real behavior
* Using:

    * DatabaseResolver
    * redis.cache profile
    * Real Redis TTL + persistence

**Tests**

* RedisIntegrationFlowTest
  Auth → Failures → Block → Unblock
* RedisTTLExpiryTest
  Real TTL expiry validation

**Rules**

* Use `DatabaseResolver->resolve('redis.cache', true)`
* Use `EnvironmentConfig(basePath)`
* No mocks, no fakes, no anonymous adapters

**Exit Criteria**

* All Redis IntegrationV2 tests pass
* Any production bug discovered → fixed in `src/`
* Legacy Redis integration tests untouched

---

### 🔒 A3 — FREEZE LEGACY INTEGRATION

**Goal**

* Prevent confusion between old and new integrations

**Actions**

* Mark `tests/Integration/Redis` as legacy
* Deprecation note in README or folder-level comment
* No deletion yet

**Exit Criteria**

* Clear separation between Integration and IntegrationV2

---

### 🔄 A4 — MYSQL INTEGRATION V2 (PLANNED)

**Scope**

* MySQLSecurityGuard behavior
* Real PDO/DBAL adapters via resolver
* Persistence, cleanup, edge cases

---

### 🔄 A5 — MONGO INTEGRATION V2 (PLANNED)

**Scope**

* MongoSecurityGuard behavior
* Real MongoDB adapter
* TTL/index behavior (if applicable)

---

## 🧠 Relationship Between Unit & Integration

| Layer         | Purpose           | Uses Fakes |
| ------------- | ----------------- | ---------- |
| Unit          | Logic correctness | ✅ Yes      |
| Coverage      | Edge paths        | ✅ Yes      |
| IntegrationV2 | Reality check     | ❌ No       |

**Important**

> Unit tests do NOT guarantee correctness of infrastructure behavior
> IntegrationV2 is the source of truth for real-world guarantees

---

## 🛑 Explicit Non-Goals

* ❌ Refactoring Unit tests right now
* ❌ Removing FakeAdapter utilities
* ❌ Forcing IntegrationV2 to reach 100% coverage
* ❌ Optimizing speed of IntegrationV2 (correctness > speed)

---

## ✅ Definition of “Done” (Global)

IntegrationV2 is considered complete when:

* Redis, MySQL, Mongo have real integration coverage
* No production bugs are hidden by mocks
* Legacy integration tests are clearly deprecated
* CI can run IntegrationV2 in infra-enabled environments

---

## 🔑 Final Rule

> **No test exists to make CI green.
> Tests exist to protect production.**

---

لو حابب، الخطوة الجاية تكون واحدة من دول (اختار):
