# Maatify Security Guard

**PSR-compliant adaptive security engine for brute-force protection, abuse detection, security event tracking, and real-time blocking — powered by unified multi-driver architecture (MySQL, Redis, MongoDB).**

![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

[![Version](https://img.shields.io/packagist/v/maatify/security-guard?label=Version&color=4C1)](https://packagist.org/packages/maatify/security-guard)
[![PHP](https://img.shields.io/packagist/php-v/maatify/security-guard?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/security-guard)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)

[![Build](https://github.com/Maatify/security-guard/actions/workflows/ci.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/security-guard/actions/workflows/ci.yml)

![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/security-guard?label=Monthly%20Downloads&color=00A8E8)
![Total Downloads](https://img.shields.io/packagist/dt/maatify/security-guard?label=Total%20Downloads&color=2AA9E0)

![Stars](https://img.shields.io/github/stars/Maatify/security-guard?label=Stars&color=FFD43B)
[![License](https://img.shields.io/github/license/Maatify/security-guard?label=License&color=blueviolet)](LICENSE)
![Status](https://img.shields.io/badge/Status-Stable-success)
![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)
![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/Maatify/security-guard/badges/coverage.json)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

---

# 🚀 Overview

**Maatify Security Guard** is a fully decoupled, high-performance, multi-driver security engine for:

- Brute-force attack protection
- Credential stuffing detection
- IP reputation & abuse control
- Distributed attack throttling
- Audit-grade event tracking (Phase 4)

The engine integrates seamlessly with:

- Native PHP
- Slim Framework
- Laravel
- Custom API Gateways
- Microservices

All storage is abstracted via:

- **maatify/data-adapters** → Real MySQL / Redis / MongoDB
- **maatify/data-fakes** → Deterministic testing engine

The library guarantees:

✔ Zero vendor lock  
✔ Zero direct database clients  
✔ Perfect testability  
✔ Real–fake execution symmetry

---

📘 **Full technical documentation:**  
➡️ [`docs/README.full.md`](docs/README.full.md)

---

# 🆕 What’s New (Phase 3 & 4 Completed)

### **🔥 Phase 3 — Drivers Layer Completed**
All storage backends are implemented:

- **MySQLSecurityGuardDriver**
- **RedisSecurityGuardDriver**
- **MongoSecurityGuardDriver**

Each driver operates strictly through the unified AdapterInterface.

---

### **🔥 Phase 4 — Unified Event System Completed**
Introduced:

- `SecurityEventDTO`
- `SecurityEventFactory`
- `SecurityAction` (extensible)
- `SecurityPlatform` (extensible)
- Built-in dispatchers:
    - `NullDispatcher`
    - `SyncDispatcher`
    - `PsrLoggerDispatcher`
- Automatic event emission inside `SecurityGuardService`

---

# 📦 Installation

```bash
composer require maatify/security-guard
````

---

# ⚡ Quick Usage

## 1️⃣ Initialize the Service

```php
$svc = new SecurityGuardService($adapter, $identifier);
```

---

## 2️⃣ Record a failed login attempt

```php
$dto = LoginAttemptDTO::now(
    ip: '192.168.1.10',
    subject: 'user@example.com',
    userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
);

$count = $svc->recordFailure($dto);
```

---

## 3️⃣ Attach an Event Dispatcher (Logging / Real-time Alerts)

```php
$svc->setEventDispatcher(
    new SyncDispatcher([
        fn(SecurityEventDTO $e) => error_log("SECURITY EVENT: " . json_encode($e)),
    ])
);
```

---

## 4️⃣ Create a manual block

```php
$svc->block(
    new SecurityBlockDTO(
        ip: '192.168.1.10',
        subject: 'user@example.com',
        type: BlockTypeEnum::MANUAL,
        expiresAt: time() + 3600,
        createdAt: time()
    )
);
```

---

## 5️⃣ Emit a custom security event

```php
$event = SecurityEventFactory::custom(
    action: SecurityAction::custom('password_reset'),
    platform: SecurityPlatform::custom('api'),
    ip: '192.168.1.10',
    subject: 'user@example.com',
    context: ['method' => 'email']
);

$svc->setEventDispatcher(new NullDispatcher());
```

---

# 🧩 Key Features

### ✔ Core Security Engine

* Adaptive brute-force handling
* Distributed blocking system
* Manual & automatic block control

### ✔ Unified DTO Layer

* LoginAttemptDTO
* SecurityBlockDTO
* SecurityEventDTO

### ✔ Unified Drivers (Phase 3)

* MySQL
* Redis
* MongoDB

### ✔ Event Pipeline (Phase 4)

* Factory-based event normalization
* Pluggable dispatchers
* Extensible actions & platforms

### ✔ Testing-Ready

* Fake drivers through `maatify/data-fakes`
* 100% deterministic behavior

---

# 🧱 Architecture

```
Application
   ↓
SecurityGuardService
   ↓
SecurityEventFactory → Dispatchers (optional)
   ↓
SecurityGuard Drivers
   ↓
AdapterInterface
   ↓
maatify/data-adapters | maatify/data-fakes
```

---

# 📄 Documentation

* 📘 **Full Documentation:** `docs/README.full.md`
* 🔬 **Examples:** `examples/Examples.md`
* 🧾 **Changelog:** `CHANGELOG.md`
* 🔐 **Security Policy:** `SECURITY.md`

<details>
<summary><strong>📚 Development Roadmap & Phase Plan</strong></summary>

### ✅ Completed Phases
- **Phase 1 – Environment Setup**
- **Phase 2 – Core Architecture & DTOs**
- **Phase 3 – Driver Implementations (MySQL / Redis / MongoDB)**
- **Phase 4 – Unified Event System + Dispatchers**

### ⏳ Upcoming
- **Phase 5 – Integration Patterns (Listeners, Pipeline Hooks)**
- **Phase 6 – Audit System (AuditEventDTO, History Store, TTL Cleanup)**
- **Phase 7–14 – Monitoring, Webhooks, SIEM, Rate Limiter Bridge**

📌 The roadmap expands automatically as the Maatify ecosystem evolves.

</details>

---

# 📅 Roadmap (Updated)

| Phase | Description                | Status      |
|-------|----------------------------|-------------|
| 1     | Environment Setup          | ✅ Completed |
| 2     | Core Architecture & DTOs   | ✅ Completed |
| 3     | Driver Implementations     | ✅ Completed |
| 4     | Event System & Dispatchers | ✅ Completed |
| 5     | Integration Patterns       | ⏳ Pending   |
| 6     | Audit System               | ⏳ Pending   |
| 7–14  | Monitoring, Webhooks, SIEM | ⏳ Pending   |

---

# 🧪 Testing

```bash
composer test
```

Runs:

* DTO tests
* Driver symmetry tests
* Factory + dispatchers tests
* Coverage reporting

---

# 🪪 License

**[MIT License](LICENSE)**
© [Maatify.dev](https://www.maatify.dev) — Free to use, modify, and distribute with attribution.

---

# 👤 Author

Developed by **Mohamed Abdulalim**
Backend Lead & Technical Architect
[https://www.maatify.dev](https://www.maatify.dev)

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a></sub>
</p>