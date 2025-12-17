@props(['data' => []])

@php
    $heading = $data['heading'] ?? '';
    $subheading = $data['subheading'] ?? '';
    $backgroundColor = $data['background_color'] ?? '#0891b2';
    $ctaButtons = $data['cta_buttons'] ?? [];
    $backgroundOrbs = $data['background_orbs'] ?? true;
@endphp

<section class="relative py-32 px-4 md:px-6 overflow-hidden scroll-reveal"
         style="background-color: {{ $backgroundColor }};">

    @if($backgroundOrbs)
        {{-- Animated monochrome mesh --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-0 w-[600px] h-[600px] rounded-full bg-gradient-radial from-white/10 to-transparent blur-3xl animate-blob"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] rounded-full bg-gradient-radial from-white/8 to-transparent blur-3xl animate-blob animation-delay-2000"></div>
        </div>
    @endif

    <div class="container mx-auto relative z-10">
        <div class="text-center space-y-8">
            <h2 class="text-5xl md:text-6xl lg:text-7xl font-light tracking-tight text-white mb-6"
                style="letter-spacing: -0.02em; line-height: 1.05;">
                {!! nl2br(e($heading)) !!}
            </h2>

            @if($subheading)
                <p class="text-xl md:text-2xl text-white/90 max-w-2xl mx-auto font-light mb-12">
                    {{ $subheading }}
                </p>
            @endif

            @if(!empty($ctaButtons))
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    @foreach($ctaButtons as $button)
                        @php
                            $buttonClass = $button['style'] === 'primary'
                                ? 'px-10 py-5 bg-white text-gray-900 font-semibold text-lg rounded-full shadow-[0_12px_32px_rgba(0,0,0,0.2),0_0_60px_rgba(255,255,255,0.2)] hover:shadow-[0_16px_40px_rgba(0,0,0,0.25),0_0_80px_rgba(255,255,255,0.25)] hover:scale-105 active:scale-95 transition-all duration-300 ios-spring'
                                : 'px-10 py-5 text-white font-semibold text-lg hover:text-white/80 transition-colors ios-spring';
                        @endphp

                        <a href="{{ $button['url'] }}" class="{{ $buttonClass }}">
                            {{ $button['text'] }}
                            @if($button['style'] === 'primary')
                                <x-heroicon-m-arrow-right class="w-5 h-5 inline ml-2" />
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
