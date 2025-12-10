@extends('layouts.app')

@section('content')
@php($marketing = $marketingContent ?? app(\App\Support\Settings\SettingsManager::class)->marketingContent())

{{-- Hero Banner (Full-screen, cinematic) --}}
<x-ios.hero-banner
    :title="$marketing['hero_title'] ?? 'Profesjonalny Detailing'"
    :subtitle="$marketing['hero_subtitle'] ?? 'Rezerwuj online. Płać po usłudze. Gwarancja satysfakcji.'"
    :primary-cta="auth()->check() ? 'Zobacz Usługi' : 'Zarezerwuj Wizytę'"
    :primary-cta-url="auth()->check() ? '#services' : route('register')"
    secondary-cta="Dowiedz się więcej"
    secondary-cta-url="#services"
    gradient="from-indigo-600 via-violet-600 to-pink-600"
>
    {{-- Trust Badges --}}
    <div class="flex flex-wrap justify-center gap-8 text-white/90">
        <div class="flex items-center gap-2 backdrop-blur-sm bg-white/10 px-4 py-2 rounded-full">
            <x-heroicon-s-star class="w-5 h-5 text-yellow-300" />
            <span class="text-sm font-medium">4.9/5 ocena</span>
        </div>
        <div class="flex items-center gap-2 backdrop-blur-sm bg-white/10 px-4 py-2 rounded-full">
            <x-heroicon-s-shield-check class="w-5 h-5 text-green-300" />
            <span class="text-sm font-medium">Gwarancja jakości</span>
        </div>
        <div class="flex items-center gap-2 backdrop-blur-sm bg-white/10 px-4 py-2 rounded-full">
            <x-heroicon-s-clock class="w-5 h-5 text-blue-300" />
            <span class="text-sm font-medium">Szybka realizacja</span>
        </div>
    </div>
</x-ios.hero-banner>

{{-- Service Grid with Glass Morphism Cards --}}
<section id="services" class="relative py-24 px-4 md:px-6 bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto">
        <div class="text-center mb-16 scroll-reveal">
            <h2 class="text-5xl md:text-6xl font-light tracking-tight text-gray-900 mb-4" style="letter-spacing: -0.02em;">
                {{ $marketing['services_heading'] ?? 'Nasze usługi' }}
            </h2>
            <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto font-light">
                {{ $marketing['services_subheading'] ?? 'Kompleksowa pielęgnacja Twojego auta na światowym poziomie' }}
            </p>
        </div>

        @if($services->isEmpty())
            <div class="max-w-2xl mx-auto bg-yellow-50 border border-yellow-200 rounded-2xl p-6 scroll-reveal">
                <div class="flex items-start gap-3">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-yellow-600 flex-shrink-0" />
                    <div>
                        <p class="font-bold text-yellow-900">Brak dostępnych usług</p>
                        <p class="mt-1 text-yellow-800">Obecnie nie mamy dostępnych usług. Sprawdź ponownie później.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($services as $service)
                    <x-ios.service-card
                        :service="$service"
                        :icon="$service->icon ?? 'sparkles'"
                        class="scroll-reveal"
                    />
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Why Choose Us Section (Split layout with parallax) --}}
<section class="relative py-24 px-4 md:px-6 overflow-hidden bg-gradient-to-br from-indigo-50 via-violet-50 to-pink-50">
    {{-- Background Orb --}}
    <div class="absolute top-0 right-0 w-[800px] h-[800px] rounded-full bg-gradient-radial from-indigo-200/30 to-transparent blur-3xl"></div>

    <div class="container mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left: Features List --}}
            <div class="space-y-8 scroll-reveal">
                <h2 class="text-5xl md:text-6xl font-light tracking-tight text-gray-900 mb-8" style="letter-spacing: -0.02em;">
                    Dlaczego <span class="font-semibold">Paradocks</span>?
                </h2>

                <div class="space-y-6">
                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center
                                    group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-s-sparkles class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">Profesjonalne produkty</h3>
                            <p class="text-gray-600">Używamy tylko sprawdzonych, premium produktów od światowych marek</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center
                                    group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-s-shield-check class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">Gwarancja jakości</h3>
                            <p class="text-gray-600">100% satysfakcji gwarantowane. Jeśli nie jesteś zadowolony, poprawimy za darmo</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-500 to-orange-500 flex items-center justify-center
                                    group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-s-clock class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">Rezerwacja online</h3>
                            <p class="text-gray-600">Zarezerwuj termin w 60 sekund. Bez telefonów, bez czekania</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 group">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center
                                    group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-s-user-group class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">Doświadczony zespół</h3>
                            <p class="text-gray-600">Nasi detailerzy mają wieloletnie doświadczenie w pielęgnacji aut premium</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Image/Visual --}}
            <div class="relative scroll-reveal">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl transform lg:translate-x-12">
                    {{-- Placeholder for hero image --}}
                    <div class="aspect-[4/3] bg-gradient-to-br from-indigo-600 via-violet-600 to-pink-600 flex items-center justify-center">
                        <div class="text-center text-white p-8">
                            <x-heroicon-o-photo class="w-24 h-24 mx-auto mb-4 opacity-50" />
                            <p class="text-lg font-medium">Miejsce na zdjęcie hero</p>
                            <p class="text-sm opacity-75">Profesjonalne zdjęcie auta po detailingu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Final CTA Section (Gradient mesh background) --}}
<section class="relative py-32 px-4 md:px-6 overflow-hidden bg-gradient-to-br from-indigo-600 via-violet-600 to-pink-600">
    {{-- Animated gradient mesh --}}
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 left-0 w-[600px] h-[600px] rounded-full bg-gradient-radial from-pink-500/40 to-transparent blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full bg-gradient-radial from-indigo-500/30 to-transparent blur-3xl animate-blob animation-delay-2000"></div>
    </div>

    <div class="container mx-auto relative z-10">
        <div class="text-center space-y-8 scroll-reveal">
            <h2 class="text-5xl md:text-6xl lg:text-7xl font-light tracking-tight text-white mb-6" style="letter-spacing: -0.02em; line-height: 1.05;">
                Gotowy na <span class="font-semibold">perfekcyjne auto</span>?
            </h2>
            <p class="text-xl md:text-2xl text-white/90 max-w-2xl mx-auto font-light mb-12">
                Zarezerwuj termin online i doświadcz profesjonalnego detailingu już dziś
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                @auth
                    <a href="{{ route('appointments.create') }}"
                       class="px-10 py-5 bg-white text-gray-900 font-semibold text-lg rounded-full
                              shadow-[0_12px_32px_rgba(0,0,0,0.2),0_0_60px_rgba(255,255,255,0.2)]
                              hover:shadow-[0_16px_40px_rgba(0,0,0,0.25),0_0_80px_rgba(255,255,255,0.25)]
                              hover:scale-105 active:scale-95 transition-all duration-300 ios-spring">
                        Zarezerwuj termin
                        <x-heroicon-m-arrow-right class="w-5 h-5 inline ml-2" />
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="px-10 py-5 bg-white text-gray-900 font-semibold text-lg rounded-full
                              shadow-[0_12px_32px_rgba(0,0,0,0.2),0_0_60px_rgba(255,255,255,0.2)]
                              hover:shadow-[0_16px_40px_rgba(0,0,0,0.25),0_0_80px_rgba(255,255,255,0.25)]
                              hover:scale-105 active:scale-95 transition-all duration-300 ios-spring">
                        Zarejestruj się teraz
                        <x-heroicon-m-arrow-right class="w-5 h-5 inline ml-2" />
                    </a>
                @endauth

                <a href="#services"
                   class="px-10 py-5 text-white font-semibold text-lg hover:text-white/80 transition-colors ios-spring">
                    Zobacz usługi
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Scroll Reveal Animations Script --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Intersection Observer for scroll reveal animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Add stagger delay based on position
                const delay = Array.from(entry.target.parentElement.children).indexOf(entry.target) * 100;
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all elements with scroll-reveal class
    document.querySelectorAll('.scroll-reveal').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});
</script>
@endpush

<style>
/* Scroll reveal animation */
.scroll-reveal.animate-in {
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Accessibility: Reduced Motion */
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

@endsection
