---
paths:
  - "resources/views/components/**"
  - "resources/views/livewire/**"
---

# Blade Components Rules

## Component Structure

### Anonymous Components
```blade
{{-- resources/views/components/button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
])

@php
$classes = match($variant) {
    'primary' => 'bg-blue-500 text-white',
    'secondary' => 'bg-gray-100 text-gray-900',
    default => 'bg-blue-500 text-white',
};
@endphp

<button {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled)>
    {{ $slot }}
</button>
```

## Livewire Compatibility

Always support Livewire directives:
- `wire:model` - Two-way data binding
- `wire:click` - Action triggers
- `wire:loading` - Loading state management

```blade
<input
    {{ $attributes->whereStartsWith('wire:model') }}
    {{ $attributes->merge(['class' => 'input-base']) }}
    type="text" />
```

## Touch Targets (Mobile-First)

- Minimum touch target: 44x44px (iOS standard)
- Use `min-h-[44px] min-w-[44px]` for interactive elements

## Design Tokens

Reference design tokens from `design-system.json` or Tailwind config:
- Colors: Use semantic names (`primary`, `secondary`, `error`)
- Spacing: Use Tailwind spacing scale
- Typography: Use configured font families
