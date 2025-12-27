---
paths:
  - "app/Http/Controllers/Auth/**"
  - "app/Http/Middleware/**"
  - "config/auth.php"
  - "config/session.php"
  - ".env.example"
---

# Security Rules

## Authentication

### Password Hashing
- Use bcrypt with 12+ rounds: `BCRYPT_ROUNDS=12`
- Never store plaintext passwords

### Session Security
- Enable encryption: `SESSION_ENCRYPT=true`
- Use secure cookies: `SESSION_SECURE_COOKIE=true` (production)
- Regenerate on login: `session()->regenerate()`

### Rate Limiting
```php
// Auth routes - strict limiting
Route::middleware(['throttle:5,1'])->group(function () {
    Auth::routes();
});
```

## Input Validation

### Never Trust User Input
```php
// BAD
$user = User::find($request->user_id);

// GOOD
$validated = $request->validated();
$user = User::findOrFail($validated['user_id']);
$this->authorize('view', $user);
```

### SQL Injection Prevention
```php
// BAD
DB::select("SELECT * FROM users WHERE email = '$email'");

// GOOD
DB::select("SELECT * FROM users WHERE email = ?", [$email]);
User::where('email', $email)->first(); // Best: Eloquent
```

## Mass Assignment

### Guarded Fields
Always guard sensitive fields in models:
```php
protected $guarded = [
    'id',
    'is_admin',
    'email_verified_at',
    'remember_token',
    'deletion_token',
];
```

## OWASP Top 10 Checklist

- [ ] A01: Broken Access Control - Use Policies
- [ ] A02: Cryptographic Failures - Encrypt sensitive data
- [ ] A03: Injection - Use parameterized queries
- [ ] A04: Insecure Design - Rate limiting enabled
- [ ] A07: Auth Failures - 2FA for admins (recommended)
