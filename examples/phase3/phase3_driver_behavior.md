# Phase 3 — Security Guard Driver Behavior Specification

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

### (MySQLSecurityGuard • RedisSecurityGuard • MongoSecurityGuard)

This document defines the **formal behavior specification** for all Phase 3 Security Guard drivers.

It is intended for:

- Contributors  
- Maintainers  
- Advanced integrators  
- Developers writing extensions or custom drivers  
- Anyone needing deterministic, cross-datastore guarantees  

**This is NOT a usage guide.**  
(Usage examples are in `docs/examples/phase3_driver_usage.md`.)

---

# 🧱 1. Architectural Overview

All Security Guard drivers must implement **identical semantics**, regardless of datastore.

```

SecurityGuardService
→ SecurityGuardDriver (Phase 3)
→ AdapterInterface (maatify/data-adapters)

```

Phase 3 drivers **never** interact with:

- PDO directly  
- Redis clients directly  
- MongoDB clients directly  

All storage actions MUST pass through:

- `MySQLAdapter`
- `RedisAdapter`
- `MongoAdapter`

This ensures:

- Deterministic behavior  
- Testability  
- Real/Fake symmetry  
- Storage abstraction  

---

# 🧩 2. Identifier Semantics

Every driver accepts:

```php
new RedisSecurityGuard($adapter, 'login.guard');
````

The identifier MUST be used as:

* The namespace for attempts
* The namespace for blocks
* The namespace for stats keys

Drivers MUST NOT change identifier format.

---

# 📘 3. Method Behavior Specification

Each driver MUST implement the following behaviors identically.

---

# 3.1 `recordFailure(LoginAttemptDTO $dto): int`

### **Input:**

* `ip`
* `subject`
* `resetAfter` (seconds)
* `userAgent` (string|null)

### **Output:**

Returns the **new attempt count** after increment.

### **Required Behavior:**

1. Combine IP + subject into a **unique key**.
2. Increment failure counter **atomically**.
3. Apply TTL equal to `resetAfter`.
4. If TTL already active, refresh it.
5. Return current count.
6. TTL expiration MUST delete the attempts record.

### **Cross-Driver Expected Behavior Table**

| Behavior           | MySQL                         | Redis       | Mongo            |
|--------------------|-------------------------------|-------------|------------------|
| Counter increment  | UPDATE/INSERT                 | atomic INCR | atomic increment |
| TTL                | manual (timestamp comparison) | EXPIRE      | TTL index        |
| Atomicity          | transaction-safe              | built-in    | built-in         |
| ResetAfter refresh | YES                           | YES         | YES              |

---

# 3.2 `resetAttempts(string $ip, string $subject): void`

### Behavior:

* Completely removes the attempts record.
* Operation MUST be idempotent.

| Driver | Behavior           |
|--------|--------------------|
| MySQL  | DELETE row         |
| Redis  | DEL key            |
| Mongo  | deleteOne document |

---

# 3.3 `block(SecurityBlockDTO $block): void`

### Requirements:

* Store full block payload:

    * ip
    * subject
    * type
    * createdAt
    * expiresAt (nullable)

### Encoding Rules:

* Drivers MUST store block payload **deterministically**.
* Field names MUST NOT change.
* Types MUST be preserved:

    * `int` timestamps
    * `string` type
    * `string|null` expiresAt

### TTL Logic:

| Scenario           | Expected Behavior            |
|--------------------|------------------------------|
| `expiresAt = null` | permanent block              |
| `expiresAt > now`  | TTL applied                  |
| expired block      | MUST be auto-removed on read |

---

# 3.4 `unblock(string $ip, string $subject): void`

### Idempotent requirement:

* If block exists → remove it
* If block does not exist → no error

| Driver | Removal Behavior |
|--------|------------------|
| MySQL  | DELETE row       |
| Redis  | DEL key          |
| Mongo  | deleteOne        |

---

# 3.5 `getStats(): array`

### MUST return:

```php
[
    'attempts' => int,
    'blocked' => bool,
    'block_expires_at' => int|null
]
```

### Cross-driver normalization rules:

* Remove expired blocks before returning stats.
* Expired counters (TTL) MUST appear as 0 attempts.
* Value types MUST be:

| Field            | Type        |
|------------------|-------------|
| attempts         | int         |
| blocked          | bool        |
| block_expires_at | int or null |

---

# 🧪 4. Expiration & TTL Semantics

### 4.1 Attempts TTL (`resetAfter`)

| Driver | Implementation                                |
|--------|-----------------------------------------------|
| MySQL  | stored timestamp; expiration checked manually |
| Redis  | EXPIRE key with exact TTL                     |
| Mongo  | TTL index deletes expired documents           |

### Required Behavior:

* All drivers MUST treat TTL expiration as **complete removal**.
* TTL MUST refresh on every failed attempt.

---

# 🔐 5. Block Expiration Rules

### Permanent Block (`expiresAt = null`)

* MUST persist indefinitely.
* MUST NOT apply TTL.
* MUST NOT auto-expire.

### Temporary Block

* MUST apply TTL if supported natively (Redis, Mongo).
* For MySQL:

    * Expired blocks MUST be deleted during `getStats()`.

---

# 🔄 6. Real vs Fake Driver Symmetry

Even though this document only discusses real drivers:

* The fake drivers MUST replicate all behaviors described here.
* Differences allowed only in:

    * microsecond timing
    * internal storage engine

---

# ⚙️ 7. Resolver Behavior

Resolver MUST map adapters to drivers:

| AdapterInstance | DriverClass        |
|-----------------|--------------------|
| MySQLAdapter    | MySQLSecurityGuard |
| RedisAdapter    | RedisSecurityGuard |
| MongoAdapter    | MongoSecurityGuard |

Behavior:

```php
if ($adapter instanceof RedisAdapter) {
    return new RedisSecurityGuard($adapter, $identifier);
}
```

Unsupported adapter MUST throw `UnsupportedDriverException`.

---

# 📐 8. Internal Invariants (MUST Always Hold)

1. No driver may silently swallow storage errors.
2. Every operation MUST be idempotent where documented.
3. Expired blocks MUST be cleaned before reporting stats.
4. Encoded block payload MUST be identical across drivers.
5. Attempt counters MUST always be integers.
6. No driver may store additional fields beyond the specification.
7. Identifier MUST NOT be altered internally.

---

# 🧩 9. Behavior Examples (Not Usage Examples)

These illustrate expected behavior, not code usage.

### Example — Expired Block

```
CreatedAt: 1700000000
ExpiresAt: 1700003600
Now:       1700007200
```

Expected:

* Block is treated as **non-existent**
* Driver MUST remove it
* `getStats()` returns:

```
blocked = false
block_expires_at = null
```

---

### Example — ResetAfter TTL

```
resetAfter = 900
LastAttemptAt = t0
Now = t0 + 901
```

Expected:

* Attempts MUST be treated as **0**
* Driver MUST auto-remove attempts record

---

# 📝 10. Summary

This specification defines:

* Required behavior for all Phase 3 drivers
* Cross-datastore normalization rules
* TTL and expiration logic
* Block persistence semantics
* Deterministic storage encoding
* Invariants required for Phase 4+

**All future driver implementations MUST conform to this document.**

---
