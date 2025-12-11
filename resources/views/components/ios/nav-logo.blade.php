@props([
    'src' => 'images/logo.svg',      // Logo image path
    'alt' => config('app.name'),     // Alt text for accessibility
    'href' => null,                  // Logo link (defaults to home route)
    'class' => '',                   // Additional CSS classes
])

@php
    // Default to home route if no href provided
    $logoHref = $href ?? route('home');

    // Build responsive logo classes
    $logoClasses = trim("h-8 lg:h-12 w-auto transition-transform hover:scale-105 {$class}");
@endphp

<a href="{{ $logoHref }}" class="flex items-center" aria-label="Go to homepage">
    <img
        src="{{ asset($src) }}"
        alt="{{ $alt }}"
        class="{{ $logoClasses }}"
        loading="eager"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
    >

    {{-- Fallback text logo (hidden by default, shown if SVG fails to load) --}}
    <span class="hidden text-xl lg:text-2xl font-bold text-cyan-500 hover:text-cyan-600 transition-colors">
        {{ $alt }}
    </span>
</a>
