@extends('layouts.app')

@section('title', $page->getEffectiveSeoTitle())

@section('meta')
    <meta name="description" content="{{ $page->getEffectiveSeoDescription() }}">

    @if($page->seo_image)
        <meta property="og:image" content="{{ asset('storage/' . $page->seo_image) }}">
        <meta property="twitter:image" content="{{ asset('storage/' . $page->seo_image) }}">
    @endif

    <meta property="og:title" content="{{ $page->getEffectiveSeoTitle() }}">
    <meta property="og:description" content="{{ $page->getEffectiveSeoDescription() }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $page->getEffectiveSeoTitle() }}">
    <meta name="twitter:description" content="{{ $page->getEffectiveSeoDescription() }}">
@endsection

@section('content')
    @forelse($page->sections ?? [] as $section)
        <x-dynamic-component
            :component="'cms.section-' . str_replace('_', '-', $section['type'])"
            :data="$section['data']"
        />
    @empty
        {{-- Fallback if no sections configured --}}
        <section class="py-24 px-4 md:px-6 bg-neutral-50">
            <div class="container mx-auto text-center">
                <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Strona główna nie jest skonfigurowana</h2>
                <p class="text-gray-600">Przejdź do panelu admina, aby dodać sekcje.</p>
                @can('viewAny', \App\Models\HomePage::class)
                    <a href="/admin/home-page"
                       class="mt-6 inline-block px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Zarządzaj stroną główną
                    </a>
                @endcan
            </div>
        </section>
    @endforelse
@endsection

{{-- Scroll Reveal Animations (reused from current home.blade.php) --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const delay = Array.from(entry.target.parentElement.children).indexOf(entry.target) * 100;
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.scroll-reveal').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});
</script>
@endpush

<style>
.scroll-reveal.animate-in {
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .scroll-reveal {
        opacity: 1 !important;
        transform: none !important;
    }
    .scroll-reveal.animate-in {
        animation: none !important;
    }
}
</style>
