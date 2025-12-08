# Phase 2 Examples

## Using DTOs

### LoginAttemptDTO

```php
use Maatify\SecurityGuard\DTO\LoginAttemptDTO;

$attempt = new LoginAttemptDTO(
    ip: '192.168.1.1',
    username: 'admin',
    userAgent: 'Mozilla/5.0'
);

echo $attempt->ip; // 192.168.1.1
```

### SecurityBlockDTO

```php
use Maatify\SecurityGuard\DTO\SecurityBlockDTO;
use DateTimeImmutable;

$block = new SecurityBlockDTO(
    ip: '192.168.1.1',
    reason: 'Too many failed attempts',
    blockedAt: new DateTimeImmutable(),
    expiresAt: new DateTimeImmutable('+30 minutes')
);

echo $block->reason; // Too many failed attempts
```
