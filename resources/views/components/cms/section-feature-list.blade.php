@props(['data' => []])

@php
    $features = $data['features'] ?? [];
    $layout = $data['layout'] ?? 'grid';
    $columns = $data['columns'] ?? '2';
    $image = $data['image'] ?? null;
    $heading = $data['heading'] ?? '';
    $subheading = $data['subheading'] ?? '';
    $backgroundColor = $data['background_color'] ?? 'neutral-50';

    $bgClass = $backgroundColor === 'white' ? 'bg-white' : 'bg-neutral-50';
    $gridClass = match($columns) {
        '3' => 'grid-cols-1 md:grid-cols-3',
        '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2',
    };
@endphp

<section class="relative py-24 px-4 md:px-6 overflow-hidden {{ $bgClass }} scroll-reveal">
    {{-- Background Orb (monochrome) --}}
    @if($backgroundColor === 'neutral-50')
        <div class="absolute top-0 right-0 w-[800px] h-[800px] rounded-full bg-gradient-radial from-primary-200/20 to-transparent blur-3xl"></div>
    @endif

    <div class="container mx-auto relative z-10">
        @if($heading || $subheading)
            <div class="text-center mb-16">
                @if($heading)
                    <h2 class="text-5xl md:text-6xl font-light tracking-tight text-gray-900 mb-4"
                        style="letter-spacing: -0.02em;">
                        {{ $heading }}
                    </h2>
                @endif

                @if($subheading)
                    <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto font-light">
                        {{ $subheading }}
                    </p>
                @endif
            </div>
        @endif

        @if($layout === 'split')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                {{-- Features List --}}
                <div class="space-y-6">
                    @foreach($features as $feature)
                        <div class="flex items-start gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-primary-500 flex items-center justify-center
                                        group-hover:scale-110 transition-transform duration-300">
                                <x-dynamic-component :component="'heroicon-s-' . ($feature['icon'] ?? 'sparkles')"
                                                     class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ $feature['title'] }}</h3>
                                <p class="text-gray-600">{{ $feature['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Image --}}
                @if($image)
                    <div class="relative scroll-reveal">
                        <div class="relative rounded-lg overflow-hidden shadow-2xl transform lg:translate-x-12">
                            <img src="{{ asset('storage/' . $image) }}"
                                 alt="{{ $heading }}"
                                 class="w-full h-auto">
                        </div>
                    </div>
                @else
                    {{-- Placeholder if no image --}}
                    <div class="relative scroll-reveal">
                        <div class="relative rounded-lg overflow-hidden shadow-2xl transform lg:translate-x-12">
                            <div class="aspect-[4/3] bg-primary-600 flex items-center justify-center">
                                <div class="text-center text-white p-8">
                                    <x-heroicon-o-photo class="w-24 h-24 mx-auto mb-4 opacity-50" />
                                    <p class="text-lg font-medium">Miejsce na zdjęcie</p>
                                    <p class="text-sm opacity-75">Dodaj zdjęcie w panelu admina</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            {{-- Grid layout --}}
            <div class="grid {{ $gridClass }} gap-8">
                @foreach($features as $feature)
                    <div class="flex items-start gap-4 group scroll-reveal">
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-primary-500 flex items-center justify-center
                                    group-hover:scale-110 transition-transform duration-300">
                            <x-dynamic-component :component="'heroicon-s-' . ($feature['icon'] ?? 'sparkles')"
                                                 class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ $feature['title'] }}</h3>
                            <p class="text-gray-600">{{ $feature['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
