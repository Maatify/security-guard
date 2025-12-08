# Security Policy — maatify/security-guard

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

At **Maatify.dev**, we take security seriously.  
This document outlines the security policy for the `maatify/security-guard` library and explains how to responsibly report vulnerabilities.

This library is designed to be a **core defensive security layer**, and any vulnerability — even theoretical — is treated with the highest priority.

---

## ✅ Supported Versions

Only the **latest stable release** of `maatify/security-guard` is supported with security updates.

| Version | Supported |
|---------|-----------|
| 1.x.x   | ✅ Yes     |
| < 1.0   | ❌ No      |

If you are using an unsupported version, you must upgrade before reporting issues.

---

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability, **DO NOT open a public GitHub issue**.

Instead, report it privately using one of the following methods:

📧 **Email:**  
**[security@maatify.dev](mailto:security@maatify.dev)**

Please include:

- A clear description of the vulnerability
- Steps to reproduce (if applicable)
- Potential impact
- Proof-of-concept (if available, responsibly)
- Affected version(s)

We aim to respond to all legitimate security reports within:

⏱ **48 hours maximum**

---

## 🛡 Responsible Disclosure

We follow a **Responsible Disclosure** policy:

1. You report the issue privately.
2. We investigate and validate the vulnerability.
3. A patch is developed and tested.
4. A fixed version is released.
5. Public disclosure is coordinated **after the fix is available**.

Please **do NOT disclose vulnerabilities publicly before a fix is released.**

---

## 🧱 Security Design Principles

`maatify/security-guard` is built on the following security principles:

- ✅ No direct database or cache clients (PDO / Redis / MongoDB)  
  All access must go through:
    - `maatify/data-adapters` (Real)
    - `maatify/data-fakes` (Testing)

- ✅ Deterministic and bounded blocking logic
- ✅ Distributed-safe IP blocking
- ✅ TTL-based expiration for all critical entries
- ✅ Immutable security DTOs
- ✅ Full auditability (MongoDB audit layer)
- ✅ Framework-agnostic core
- ✅ Zero hidden side-effects

---

## 🧪 Security Testing

This project enforces:

- ✅ Unit tests using **Fake Adapters**
- ✅ Integration tests using **Real Adapters**
- ✅ PHPStan **Level MAX**
- ✅ CI enforcement for:
    - Tests
    - Coverage
    - Static analysis

Security regressions automatically fail CI.

---

## 🔐 Scope of This Policy

This policy applies strictly to:

- `maatify/security-guard`
- All official releases under `Maatify/security-guard`
- All official integration bridges (e.g., rate limiter bridge, audit module)

It does **NOT** cover:

- User-land application misuse
- Infrastructure misconfiguration
- Unsafe server environments
- Weak passwords on the consuming application layer

---

## ⚠️ Unsupported & Forbidden Practices

The following are **explicitly forbidden** inside this library:

- ❌ Direct PDO usage
- ❌ Direct Redis client usage
- ❌ Direct MongoDB\Client usage
- ❌ Silent failure of security events
- ❌ Suppressing security-related exceptions
- ❌ Weak or unbounded blocking logic

Any PR introducing such behavior will be rejected immediately.

---

## 🏷 CVE Handling

If a vulnerability qualifies for a CVE:

- The Maatify security team will coordinate CVE assignment.
- The CVE ID will be published in:
    - `CHANGELOG.md`
    - GitHub Security Advisories
    - Release Notes

---

## 🙏 Security Researchers & Credits

We deeply appreciate the efforts of security researchers who responsibly disclose vulnerabilities.

With your permission, we are happy to:

- Credit you in the release notes
- Acknowledge your contribution in `CHANGELOG.md`

---

## 📜 Legal

This security policy is subject to change at any time without prior notice.  
By using this library, you agree that:

- You use it **at your own risk**
- No warranty is provided
- Liability is limited as per the project license (MIT)

---

<p align="center">
  <sub>Security-first engineering by <a href="https://www.maatify.dev">Maatify.dev</a></sub>
</p>
