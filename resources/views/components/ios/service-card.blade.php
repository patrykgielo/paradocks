@props([
    'service' => null,
    'icon' => 'sparkles',
    'title' => '',
    'description' => '',
    'price' => null,
    'duration' => null,
    'url' => '#',
    'showCta' => true,
])

@php
    // Extract from service model if provided
    if ($service) {
        $title = $service->name;
        $description = $service->excerpt ?? Str::limit($service->description, 100);
        $price = $service->price_from ?? $service->price;
        $duration = $service->duration_minutes;
        $url = route('service.show', $service->slug ?? $service->id);
        $icon = $service->icon ?? 'sparkles';

        // Conversion optimization data
        $averageRating = $service->average_rating ?? 0;
        $totalReviews = $service->total_reviews ?? 0;
        $isPopular = $service->is_popular ?? false;
        $bookingCountWeek = $service->booking_count_week ?? 0;
        $features = $service->features ?? [];
    } else {
        $averageRating = 0;
        $totalReviews = 0;
        $isPopular = false;
        $bookingCountWeek = 0;
        $features = [];
    }

    // Icon gradient colors based on service type
    $gradientMap = [
        'sparkles' => 'from-blue-500 to-blue-600',
        'rectangle-stack' => 'from-green-500 to-green-600',
        'paint-brush' => 'from-purple-500 to-purple-600',
        'sun' => 'from-yellow-500 to-yellow-600',
        'squares-plus' => 'from-pink-500 to-pink-600',
        'swatch' => 'from-indigo-500 to-indigo-600',
        'beaker' => 'from-teal-500 to-teal-600',
        'shield-check' => 'from-orange-500 to-orange-600',
    ];
    $gradient = $gradientMap[$icon] ?? 'from-gray-500 to-gray-600';
@endphp

<article
    @class([
        'service-card',
        'service-card--popular' => $isPopular,
        'group relative bg-white rounded-2xl p-6',
        'shadow-md hover:shadow-2xl',
        'hover:-translate-y-2 transition-all duration-300',
        'ios-spring border border-gray-100 hover:border-orange-300',
        'cursor-pointer overflow-hidden',
    ])
    x-data="{ hover: false }"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
    @click="window.location.href = '{{ $url }}'"
>
    {{-- Popularity Badge --}}
    @if($isPopular)
    <div class="service-card__badge absolute top-4 right-4 z-10 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1">
        <x-heroicon-s-star class="service-card__badge-icon w-3 h-3" />
        <span class="service-card__badge-text">Najpopularniejsze</span>
    </div>
    @endif

    {{-- Icon Container (iOS App Icon Style) --}}
    <div class="service-card__icon flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br {{ $gradient }} mb-4 transition-transform duration-300 ios-spring group-hover:scale-110 group-hover:rotate-3 shadow-lg">
        @switch($icon)
            @case('sparkles')
                <x-heroicon-s-sparkles class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('rectangle-stack')
                <x-heroicon-s-rectangle-stack class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('paint-brush')
                <x-heroicon-s-paint-brush class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('sun')
                <x-heroicon-s-sun class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('squares-plus')
                <x-heroicon-s-squares-plus class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('swatch')
                <x-heroicon-s-swatch class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('beaker')
                <x-heroicon-s-beaker class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @case('shield-check')
                <x-heroicon-s-shield-check class="service-card__icon-svg w-8 h-8 text-white" />
                @break
            @default
                <x-heroicon-s-sparkles class="service-card__icon-svg w-8 h-8 text-white" />
        @endswitch
    </div>

    {{-- Star Rating (extracted component, hidden for now per user request) --}}
    {{--
    <div class="service-card__rating mb-3">
        <x-ios.star-rating
            :rating="$averageRating"
            :total-reviews="$totalReviews"
            size="sm"
        />
    </div>
    --}}

    {{-- Service Title --}}
    <h3 class="service-card__title text-xl font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors duration-200">
        {{ $title }}
    </h3>

    {{-- Description --}}
    <p class="service-card__description text-gray-600 text-sm mb-3 line-clamp-2 leading-relaxed">
        {{ $description }}
    </p>

    {{-- Duration Badge --}}
    @if($duration)
    <div class="service-card__duration flex items-center gap-1.5 text-xs text-gray-600 mb-4 bg-gray-50 px-3 py-1.5 rounded-lg w-fit">
        <x-heroicon-m-clock class="service-card__duration-icon w-4 h-4 text-gray-500" />
        <span class="service-card__duration-text font-medium">{{ $duration }} min</span>
    </div>
    @endif

    {{-- Features List (hidden on mobile, visible on md+) --}}
    @if(is_array($features) && count($features) > 0)
    <ul class="service-card__features hidden md:block space-y-2 mb-4 bg-gray-50 rounded-xl p-4">
        @foreach(array_slice($features, 0, 4) as $feature)
        <li class="service-card__feature flex items-start gap-2 text-xs text-gray-700">
            <x-heroicon-s-check-circle class="service-card__feature-icon w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" />
            <span class="service-card__feature-text leading-relaxed">{{ $feature }}</span>
        </li>
        @endforeach
    </ul>
    @endif

    {{-- Price --}}
    @if($price)
    <div class="service-card__price flex items-baseline gap-1 mb-4">
        <span class="service-card__price-label text-sm text-gray-600 font-medium">Od</span>
        <span class="service-card__price-value text-3xl font-bold text-gray-900">{{ number_format($price, 0, ',', ' ') }}</span>
        <span class="service-card__price-currency text-sm text-gray-600 font-medium">zł</span>
    </div>
    @endif

    {{-- CTA Button --}}
    @if($showCta)
    <a
        href="{{ $url }}"
        class="service-card__cta flex items-center justify-center gap-2 w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold text-sm py-3.5 px-4 rounded-xl hover:from-orange-600 hover:to-orange-700 hover:shadow-lg transition-all duration-200 ios-spring"
        @click.stop
    >
        <span class="service-card__cta-text">Zobacz Szczegóły</span>
        <x-heroicon-m-arrow-right class="service-card__cta-icon w-4 h-4" />
    </a>
    @endif

    {{-- Urgency Footer --}}
    @if($bookingCountWeek > 0)
    <div class="service-card__urgency flex items-center justify-center gap-1.5 mt-3 text-xs text-gray-600 bg-orange-50 py-2 px-3 rounded-lg">
        <x-heroicon-m-fire class="service-card__urgency-icon w-4 h-4 text-orange-500" />
        <span class="service-card__urgency-text">
            Zarezerwowano <strong class="service-card__urgency-count text-orange-700 font-bold">{{ $bookingCountWeek }} razy</strong> w tym tygodniu
        </span>
    </div>
    @endif
</article>
