---
paths:
  - "database/migrations/**"
---

# Database Migration Rules

## Security First

### Never in Migrations
- Raw SQL without bindings: `DB::statement("DELETE FROM users WHERE id = $id")`
- Plaintext passwords or secrets
- Default passwords in seeds that might leak to production

### Always Use
- Parameterized queries: `DB::statement("DELETE FROM users WHERE id = ?", [$id])`
- Environment variables for secrets
- Faker for test data, not real data

## Naming Conventions

```
YYYY_MM_DD_HHMMSS_action_table_column.php

Examples:
2025_01_15_100000_create_appointments_table.php
2025_01_15_100001_add_status_to_appointments_table.php
2025_01_15_100002_modify_duration_on_appointments_table.php
```

## Column Best Practices

### Indexes
- Foreign keys: Always add index
- Frequently queried: Add index
- Unique constraints: Use `unique()` not just index

```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->string('email')->unique();
$table->index(['status', 'created_at']); // Compound index
```

### Soft Deletes
- Use `softDeletes()` for important records (users, appointments)
- Consider retention policies for GDPR compliance

### Timestamps
- Always use `timestamps()` for created_at/updated_at
- Add `deleted_at` with `softDeletes()` if needed

## Rollback Safety

Always implement `down()` method:
```php
public function down(): void
{
    Schema::dropIfExists('appointments');
}
```
