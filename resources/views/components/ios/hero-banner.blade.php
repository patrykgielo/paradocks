@props([
    'title' => 'Detailing jak z przyszłości',
    'subtitle' => 'Profesjonalne usługi detailingowe dla Twojego auta',
    'primaryCta' => null,
    'secondaryCta' => null,
    'primaryCtaUrl' => '#',
    'secondaryCtaUrl' => '#',
    'gradient' => 'from-indigo-600 via-violet-600 to-pink-600',
])

{{-- Full-screen cinematic hero (Apple.com style) --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br {{ $gradient }}">
    {{-- Noise Texture Overlay (App Store style) --}}
    <div class="absolute inset-0 opacity-5 mix-blend-overlay" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=\'0 0 400 400\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'noiseFilter\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.9\' numOctaves=\'4\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23noiseFilter)\'/%3E%3C/svg%3E');"></div>

    {{-- Animated Gradient Orbs (iOS 17 style) --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] rounded-full bg-gradient-radial from-indigo-500/40 via-indigo-500/20 to-transparent blur-3xl animate-blob"></div>
        <div class="absolute top-1/3 right-1/4 w-[500px] h-[500px] rounded-full bg-gradient-radial from-violet-500/30 via-violet-500/15 to-transparent blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-1/4 left-1/3 w-[550px] h-[550px] rounded-full bg-gradient-radial from-pink-500/25 via-pink-500/10 to-transparent blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    {{-- Content Container --}}
    <div class="relative z-10 container mx-auto px-4 md:px-6 py-32 text-center">
        {{-- Headline (iOS typography scale - large, light) --}}
        <h1 class="text-6xl md:text-7xl lg:text-8xl font-light tracking-tight text-white mb-6 animate-fade-in-up"
            style="animation-delay: 0.1s; letter-spacing: -0.02em; line-height: 1.05;">
            {{ $title }}
        </h1>

        {{-- Subtitle --}}
        @if($subtitle)
        <p class="text-xl md:text-2xl lg:text-3xl text-white/80 mb-12 max-w-3xl mx-auto font-light animate-fade-in-up"
           style="animation-delay: 0.2s; line-height: 1.4;">
            {{ $subtitle }}
        </p>
        @endif

        {{-- CTA Buttons (glass morphism primary, text secondary) --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-fade-in-up" style="animation-delay: 0.3s;">
            @if($primaryCta)
            <a href="{{ $primaryCtaUrl }}"
               class="group relative px-8 py-4 bg-white/20 backdrop-blur-xl rounded-full text-white font-semibold text-lg
                      shadow-[0_8px_24px_rgba(0,0,0,0.15),0_0_40px_rgba(255,255,255,0.1)]
                      hover:bg-white/30 hover:shadow-[0_12px_32px_rgba(0,0,0,0.2),0_0_60px_rgba(255,255,255,0.15)]
                      hover:scale-105 active:scale-95
                      transition-all duration-300 ios-spring
                      border border-white/30">
                <span class="relative z-10">{{ $primaryCta }}</span>
                {{-- Subtle shine effect on hover --}}
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            </a>
            @endif

            @if($secondaryCta)
            <a href="{{ $secondaryCtaUrl }}"
               class="px-8 py-4 text-white font-semibold text-lg hover:text-white/80 transition-colors ios-spring
                      flex items-center gap-2">
                {{ $secondaryCta }}
                <x-heroicon-m-arrow-right class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
            </a>
            @endif
        </div>

        {{-- Trust Badges (optional slot) --}}
        @if($slot->isNotEmpty())
        <div class="mt-16 animate-fade-in-up" style="animation-delay: 0.4s;">
            {{ $slot }}
        </div>
        @endif

        {{-- Scroll Indicator (subtle, animated) --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <x-heroicon-o-chevron-down class="w-6 h-6 text-white/50" />
        </div>
    </div>
</section>
