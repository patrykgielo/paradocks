@props(['data' => []])

@php
    use App\Models\Service;
    use App\Models\Post;
    use App\Models\Promotion;
    use App\Models\PortfolioItem;

    $contentType = $data['content_type'] ?? 'services';
    $contentItemIds = $data['content_items'] ?? [];
    $columns = $data['columns'] ?? '3';
    $heading = $data['heading'] ?? '';
    $subheading = $data['subheading'] ?? '';
    $backgroundColor = $data['background_color'] ?? 'white';

    // Fetch content items in specified order (FIELD preserves order)
    $items = collect([]);
    if (!empty($contentItemIds)) {
        $items = match($contentType) {
            'services' => Service::whereIn('id', $contentItemIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $contentItemIds)) . ')')
                ->get(),
            'posts' => Post::whereIn('id', $contentItemIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $contentItemIds)) . ')')
                ->get(),
            'promotions' => Promotion::whereIn('id', $contentItemIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $contentItemIds)) . ')')
                ->get(),
            'portfolio' => PortfolioItem::whereIn('id', $contentItemIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_map('intval', $contentItemIds)) . ')')
                ->get(),
            default => collect([]),
        };
    }

    $bgClass = match($backgroundColor) {
        'neutral-50' => 'bg-neutral-50',
        'primary-50' => 'bg-primary-50',
        default => 'bg-white',
    };

    $gridClass = match($columns) {
        '2' => 'grid-cols-1 md:grid-cols-2',
        '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    };
@endphp

<section class="relative py-24 px-4 md:px-6 {{ $bgClass }} scroll-reveal">
    <div class="container mx-auto">
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

        @if($items->isEmpty())
            <div class="max-w-2xl mx-auto bg-yellow-50 border border-yellow-200 rounded-2xl p-6">
                <div class="flex items-start gap-3">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-yellow-600 flex-shrink-0" />
                    <div>
                        <p class="font-bold text-yellow-900">Brak elementów</p>
                        <p class="mt-1 text-yellow-800">Wybrane elementy nie istnieją lub zostały usunięte.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="grid {{ $gridClass }} gap-8">
                @foreach($items as $item)
                    @if($contentType === 'services')
                        <x-ios.service-card :service="$item" class="scroll-reveal" />
                    @else
                        {{-- Basic card for posts, promotions, portfolio --}}
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 scroll-reveal">
                            @if(isset($item->featured_image))
                                <img src="{{ asset('storage/' . $item->featured_image) }}"
                                     alt="{{ $item->title }}"
                                     class="w-full h-48 object-cover">
                            @endif
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $item->title ?? $item->name }}</h3>
                                <p class="text-gray-600 mb-4">{{ Str::limit($item->excerpt ?? $item->body ?? '', 100) }}</p>
                                @if($item->slug ?? false)
                                    <a href="{{ route(match($contentType) {
                                            'posts' => 'post.show',
                                            'promotions' => 'promotion.show',
                                            'portfolio' => 'portfolio.show',
                                            default => 'home'
                                        }, $item->slug) }}"
                                       class="text-primary-600 hover:text-primary-700 font-semibold">
                                        Czytaj więcej →
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
