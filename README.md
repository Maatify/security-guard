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

📘 Looking for the complete technical documentation?  
➡️ **[Read the Full Documentation](docs/README.full.md)**

---

## ✅ Planned Supported Storage Backends (Phase 3)

| Backend | Layer Type  | Use Case                              |
|---------|-------------|---------------------------------------|
| Redis   | Real Driver | High-speed IP blocking & counters     |
| MongoDB | Real Driver | Security audit & time-series analysis |
| MySQL   | Real Driver | Persistent compliance & forensic logs |

⚠️ All drivers listed above are planned for Phase 3 and are not yet available in the current release.

> ❗ Direct usage of PDO, Redis clients, or MongoDB clients is **forbidden** inside this library.
---

# 📦 Installation

```bash
composer require maatify/security-guard
```

---

# ⚡ Quick Usage

⚠️ Usage examples will be available starting from **Phase 4** after the
`SecurityGuardService` and resolver layer are finalized.

📘 **Full usage examples (Native, API, Middleware, Rate Limiter Bridge):**
➡️ **[examples/Examples.md](examples/Examples.md)**

---

# 🧩 Key Features

* Adaptive brute-force protection
* Distributed IP-based blocking
* (Planned) Multi-driver resolver (Redis / MongoDB / MySQL)
* Unified attempt / block / reset API
* DTO-based security events
* (Planned) PSR-3 logging support
* (Planned) Telegram & Webhook alerts (optional)
* (Planned) Rate Limiter bridge support
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

* 📘 **[Full Documentation](docs/README.full.md)** — Complete architecture, adapters, audits, and advanced usage
* 📚 **[Usage Examples](examples/Examples.md)** — Native, API, Middleware & Integration examples
* 🧾 **[Changelog](CHANGELOG.md)** — Full version history
* 🔐 **[Security Policy](SECURITY.md)** — Vulnerability reporting & security rules


<details>
<summary><strong>📚 Development Roadmap & Phase Plan</strong></summary>

✅ Phase 1 – Environment Setup (Completed)  
✅ Phase 2 – Core Architecture & DTOs (Completed)

⚠️ All subsequent phases are planned and not yet released.

</details>

---

# 🧪 Testing

```bash
composer test
```

Runs:

* DTO validation tests
* Contract interface tests
* JSON serialization tests
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