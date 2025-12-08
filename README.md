# Maatify Security Guard

**PSR-compliant adaptive security engine for brute-force protection, abuse detection, and real-time blocking using Redis, MongoDB, and MySQL via unified adapters.**

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
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/security-guard/main?color=brightgreen)](https://www.codefactor.io/repository/github/Maatify/security-guard)

![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)
![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/Maatify/security-guard/badges/coverage.json)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

---

# 🚀 Overview

**Maatify Security Guard** is a fully decoupled, adaptive security protection engine designed to prevent:

- Brute-force login attacks
- Credential stuffing
- IP-based abuse
- Burst and distributed attack patterns

It integrates seamlessly with:

- Native PHP
- Slim Framework
- Laravel
- Custom API Gateways

All storage is handled through:

- ✅ **maatify/data-adapters (Real)**
- ✅ **maatify/data-fakes (Testing / Simulation)**

---

## ✅ Supported Storage Backends

| Backend | Layer Type  | Use Case                              |
|---------|-------------|---------------------------------------|
| Redis   | Real Driver | High-speed IP blocking & counters     |
| MongoDB | Real Driver | Security audit & time-series analysis |
| MySQL   | Real Driver | Persistent compliance & forensic logs |

> ❗ Direct usage of PDO, Redis clients, or MongoDB clients is **forbidden** inside this library.

---

# 📦 Installation

```bash
composer require maatify/security-guard
````

---

# ⚡ Quick Usage

```php
use Maatify\SecurityGuard\Resolver\SecurityGuardResolver;

$resolver = new SecurityGuardResolver(['driver' => 'redis']);
$guard    = $resolver->resolve();

$guard->handleAttempt(
    ip: '127.0.0.1',
    action: 'login',
    platform: 'web'
);

if ($guard->isBlocked('127.0.0.1')) {
    echo 'Access Blocked';
}
```

📘 **Full usage examples (Native, API, Middleware, Rate Limiter Bridge):**
➡️ **[examples/Examples.md](examples/Examples.md)**

---

# 🧩 Key Features

* Adaptive brute-force protection
* Distributed IP-based blocking
* Multi-driver resolver (Redis / MongoDB / MySQL)
* Unified attempt / block / reset API
* DTO-based security events
* PSR-3 logging support
* Telegram & Webhook alerts (optional)
* Rate Limiter bridge support
* PHPStan Level Max ready
* 100% adapter-driven storage

---

# 🧱 Architecture

| Layer          | Library                 |
|----------------|-------------------------|
| Storage (Real) | `maatify/data-adapters` |
| Storage (Fake) | `maatify/data-fakes`    |
| Contracts      | `maatify/common`        |
| Rate Limiting  | `maatify/rate-limiter`  |

---

# 📄 Documentation

* [**Changelog**](CHANGELOG.md)
* [**Security Policy**](SECURITY.md)
* [**Usage Examples**](examples/Examples.md)

<details>
<summary><strong>📚 Development History & Phase Details</strong></summary>

* Phase 1 – Environment Setup
* Phase 2 – Core Architecture & DTOs
* Phase 3 – Adapter-based Drivers
* Phase 4 – Core Security Logic
* Phase 5 – Rate Limiter Bridge
* Phase 6 – Audit DTO & Storage
* Phase 7 – Mongo Audit Forwarding
* Phase 8 – Audit History APIs
* Phase 9 – Audit Filters & Indexing
* Phase 10 – PSR Logger Integration
* Phase 11 – Telegram Alerts
* Phase 12 – Webhook Dispatcher
* Phase 13 – Retry Engine
* Phase 14 – Monitoring APIs
* Phase 15 – Consistency Tests
* Phase 16 – Attack Simulations
* Phase 17 – Stress Testing
* Phase 18 – Coverage Hardening
* Phase 19 – Packagist Release

</details>

---

# 🧪 Testing

```bash
composer test
```

Runs:

* Fake adapter attack simulations
* Real adapter stress validation
* Resolver switching tests
* Webhook retry tests
* Coverage reporting

---

## 🪪 License

**[MIT License](LICENSE)**
© [Maatify.dev](https://www.maatify.dev) — Free to use, modify, and distribute with attribution.

---

## 👤 Author

Engineered by **Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))
Backend Lead & Technical Architect — [https://www.maatify.dev](https://www.maatify.dev)

---

## 🤝 Contributors

Special thanks to the Maatify.dev engineering team and all open-source contributors.

Before submitting a Pull Request, please read:

* [Contributing Guide](CONTRIBUTING.md)
* [Code of Conduct](CODE_OF_CONDUCT.md)

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>