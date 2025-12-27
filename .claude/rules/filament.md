---
paths:
  - "app/Filament/**"
  - "**/Filament/**"
---

# Filament v4 Rules

When working with Filament files, ALWAYS follow these rules:

## Critical Namespace Changes (v4 Breaking)

### Layout Components → `Filament\Schemas\Components\*`
```php
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Fieldset;
```

### Data Entry Components → `Filament\Infolists\Components\*`
```php
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
```

### Form Components → `Filament\Forms\Components\*`
```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
```

## Widget Rules (Avoid Recent Bug)

- Widgets are top-level components with built-in layout
- **NEVER** nest `<x-filament::section>` as root element in widgets
- Use `<x-filament-widgets::widget>` wrapper in Blade templates
- Heading/description go to widget slots, NOT section component

## Documentation References

- [Component Architecture](docs/guides/filament-v4-component-architecture.md)
- [Migration Guide](docs/guides/filament-v4-migration-guide.md)
- [Best Practices](docs/guides/filament-v4-best-practices.md)
- [Widgets Guide](docs/guides/filament-v4-widgets-guide.md)
