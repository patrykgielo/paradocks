@props(['data' => []])

@php
    $content = $data['content'] ?? '';
    $layout = $data['layout'] ?? 'default';
    $backgroundColor = $data['background_color'] ?? 'white';

    $bgClass = match($backgroundColor) {
        'neutral-50' => 'bg-neutral-50',
        'primary-50' => 'bg-primary-50',
        default => 'bg-white',
    };

    $containerClass = match($layout) {
        'full-width' => 'max-w-full',
        'narrow' => 'max-w-3xl',
        default => 'max-w-5xl',
    };
@endphp

<section class="relative py-24 px-4 md:px-6 {{ $bgClass }} scroll-reveal">
    <div class="container mx-auto">
        <div class="{{ $containerClass }} mx-auto">
            <div class="prose prose-lg prose-primary max-w-none">
                {!! $content !!}
            </div>
        </div>
    </div>
</section>
