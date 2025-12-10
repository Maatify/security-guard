# 📘 Maatify Security Guard – Usage Examples

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

---

### 📂 **Looking for full detailed examples?**

➡️ See the complete Phase 4 examples directory:
**[`examples/phase4/README.md`](phase4/README.md)**

---

This document provides **real-world usage examples** for
`maatify/security-guard` using both:

* ✅ Real Adapters (`maatify/data-adapters`)
* ✅ Fake Adapters (`maatify/data-fakes`)

---

## 1️⃣ Native PHP – Real Security Guard (Redis)

```php
use Maatify\SecurityGuard\Resolver\SecurityGuardResolver;
use Maatify\SecurityGuard\Enums\SecurityActionEnum;

$config = [
    'driver' => 'redis'
];

$resolver = new SecurityGuardResolver($config);
$guard = $resolver->resolve(); // Real adapter

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$status = $guard->handleAttempt($ip, SecurityActionEnum::LOGIN);

if ($status->isBlocked) {
    echo "⛔ Blocked until: {$status->blockedUntil}";
} else {
    echo "✅ Allowed. Remaining attempts: {$status->remaining}";
}
```

---

## 2️⃣ Native PHP – Fake Security Guard (Testing / CI)

```php
use Maatify\SecurityGuard\Resolver\SecurityGuardResolver;

$config = [
    'driver' => 'fake'
];

$resolver = new SecurityGuardResolver($config);
$guard = $resolver->resolve(); // Fake adapter via data-fakes

$status = $guard->handleAttempt('ip-test-1', 'login');

assert($status->remaining === 4);
```

✅ Used in:

* Unit Testing
* Simulation
* CI

---

## 3️⃣ Auto Block After Threshold

```php
for ($i = 1; $i <= 6; $i++) {
    $status = $guard->handleAttempt('192.168.0.1', 'login');
    echo "Attempt $i → Remaining: {$status->remaining}\n";
}
```

✅ After threshold:

```
Attempt 5 → Remaining: 0
Attempt 6 → BLOCKED
```

---

## 4️⃣ Reset on Success

```php
$guard->reset('192.168.0.1', 'login');
```

✅ Clears:

* Fail counter
* Block status
* TTL entries

---

## 5️⃣ Rate Limiter Bridge Integration

```php
use Maatify\SecurityGuard\Bridge\RateLimiterBridge;
use Maatify\RateLimiter\Resolver\RateLimiterResolver;

$limiter = (new RateLimiterResolver(['driver' => 'redis']))->resolve();

$bridge = new RateLimiterBridge($limiter);
$bridge->onSecurityBlock($ip, 'login');
```

✅ No DB coupling
✅ Event-based only

---

## 6️⃣ Audit Logging (Mongo via Adapter)

```php
use Maatify\SecurityGuard\Audit\AuditHistoryService;

$audit = new AuditHistoryService();
$events = $audit->getByIp('192.168.0.1');

foreach ($events as $event) {
    echo $event->action . " @ " . $event->createdAt;
}
```

---

## 7️⃣ Fake Attack Simulation

```php
for ($i = 1; $i <= 20; $i++) {
    $guard->handleAttempt('bot-ip', 'login');
}
```

✅ Used in:

* Phase 16 (Attack Simulation)
* Phase 17 (Stress)

---

## 8️⃣ Environment Configuration

```env
SECURITY_MAX_ATTEMPTS=5
SECURITY_BLOCK_TTL=300
SECURITY_AUDIT_DRIVER=mongo
SECURITY_NOTIFY_TELEGRAM=true
```

---

## 9️⃣ Real vs. Fake Summary

| Mode | Uses                    | Library                 |
|------|-------------------------|-------------------------|
| Real | Production              | `maatify/data-adapters` |
| Fake | Tests / CI / Simulation | `maatify/data-fakes`    |

---

## ✅ Related Documentation

* Main README → `README.md`
* Security Policy → `SECURITY.md`
* Changelog → `CHANGELOG.md`
* Contributing → `CONTRIBUTING.md`
* **Phase 4 Examples → [`examples/phase4/README.md`](phase4/README.md)**

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a></sub>
</p>