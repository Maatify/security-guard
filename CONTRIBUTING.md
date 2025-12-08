# Contributing to maatify/security-guard

[![Maatify Security Guard](https://img.shields.io/badge/Maatify-Security--Guard-blue?style=for-the-badge)](https://github.com/Maatify/security-guard)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

Thank you for considering contributing to **maatify/security-guard**!  
This project follows **strict security, architectural, and testing standards** to guarantee reliability, determinism, and production-grade protection across the entire Maatify ecosystem.

Please read the following guidelines carefully before submitting issues, feature proposals, or pull requests.

---

# 📌 Ways to Contribute

You can contribute by:

- Reporting security bugs or logic flaws
- Improving brute-force and abuse detection logic
- Proposing new blocking strategies or drivers
- Improving documentation and examples
- Submitting pull requests (bug fixes, optimizations, refactoring)
- Writing fake adapter simulations and stress tests
- Improving alerting, logging, and webhook systems

---

# 🧱 Project Structure

```

src/              Main security guard engine
tests/            PHPUnit test suite (Fake + Real)
docs/             Documentation & phase history
examples/         Practical usage examples
build/            Phase outputs & patches

````

Main references:

👉 [`README.md`](README.md)  
👉 [`examples/Examples.md`](examples/Examples.md)

---

# 🧪 Running Tests

Before submitting any pull request, make sure all tests pass:

```bash
composer install
composer test
````

To run static analysis:

```bash
composer run analyse
```

Minimum requirements:

* **PHPStan level: MAX** (no errors allowed)
* **PHPUnit**: all tests must pass
* **Coverage**: no regression allowed
* **Driver consistency**: Redis / MongoDB / MySQL behaviors must remain aligned
* **Fake vs Real parity** is strictly enforced

---

# 🧹 Code Style

This project enforces:

* **PSR-12** coding standards
* **Strict Types** (`declare(strict_types=1)`)
* **Strong typing only** (no weak mixed usage)
* **Immutable DTOs**
* **Resolver-first architecture**
* **Zero direct database client usage**
* **Full AdapterInterface compliance**

Before pushing your changes:

```bash
composer run lint
composer run format
```

---

# 🧬 Commit Messages

Use clear, descriptive commit messages.

Recommended format:

```
type(scope): short description

Optional detailed explanation
```

Examples:

* `fix(redis-driver): prevent premature unblock`
* `feat(alerts): add telegram critical channel`
* `docs(audit): expand mongo audit examples`
* `refactor(resolver): unify fake/real switching`

---

# 🌱 Branching Model

We use the following branching workflow:

* `main` → stable releases
* `develop` → active development
* Feature branches:
  `feature/<short-name>`
* Bugfix branches:
  `fix/<short-name>`
* Security hotfixes:
  `hotfix/<short-name>`

---

# 🔀 Pull Request Guidelines

Before opening a PR:

1. Ensure code passes **all tests & static analysis**
2. Follow **PSR-12 + project architectural rules**
3. Add or update tests for **every behavior change**
4. Update documentation if your change affects usage
5. Keep PRs **small, focused, and reviewable**
6. Reference related issues when applicable
7. Add a clear PR description explaining:

    * What changed
    * Why it was necessary
    * How it was tested
    * Any backward compatibility impact
    * Fake vs Real behavior verification

PRs that fail CI, reduce coverage, or violate architectural rules will be rejected.

---

# 🧩 Architectural Rules

All contributors **MUST** follow these core rules:

* All storage goes through:

    * ✅ `maatify/data-adapters` (Real)
    * ✅ `maatify/data-fakes` (Testing)
* ❌ Direct PDO / Redis / MongoDB clients are forbidden
* Unified security flow:

    * `handleAttempt()`
    * `isBlocked()`
    * `reset()`
* IP blocking must be:

    * Deterministic
    * Distributed-safe
    * TTL bounded
* Fake simulations must match real behavior exactly
* Security events must remain:

    * Serializable
    * Auditable
    * Replay-safe
* Middleware & hooks must remain framework-agnostic

---

# 🗂 Versioning

We follow **Semantic Versioning (SemVer)**:

```
MAJOR.MINOR.PATCH
```

* **PATCH** → Bug fixes & internal improvements
* **MINOR** → Backward-compatible security features
* **MAJOR** → Breaking API or security model changes

All releases must be documented in:

👉 `CHANGELOG.md`

---

# 🔒 Security Vulnerabilities

To report a security issue, **DO NOT** open a public GitHub issue.

Instead, contact:

📧 **[security@maatify.dev](mailto:security@maatify.dev)**

Also see:

👉 [`SECURITY.md`](SECURITY.md)

---

# 🙏 Thank You!

Your contributions help make the Maatify ecosystem more **secure, resilient, and production-ready**.
We deeply appreciate your time, expertise, and commitment to clean, defensive architecture.

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>