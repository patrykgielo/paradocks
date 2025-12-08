@extends('layouts.app')

@push('head')
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $service->meta_title ?? $service->name }}">
    <meta property="og:description" content="{{ $service->meta_description ?? $service->excerpt }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('service.show', $service) }}">
    @if($service->featured_image)
        <meta property="og:image" content="{{ Storage::url($service->featured_image) }}">
    @endif

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $service->meta_description ?? $service->excerpt }}">
    <title>{{ $service->meta_title ?? $service->name . ' - ' . config('app.name') }}</title>

    <!-- Schema.org Service JSON-LD -->
    <script type="application/ld+json">{!! $schemaService !!}</script>

    <!-- Schema.org BreadcrumbList JSON-LD -->
    <script type="application/ld+json">{!! $schemaBreadcrumbs !!}</script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumbs HTML -->
    <nav class="mb-6 text-sm" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-gray-600">
            <li>
                <a href="{{ route('home') }}" class="hover:text-blue-600">Strona główna</a>
            </li>
            <li>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </li>
            <li>
                <a href="{{ route('services.index') }}" class="hover:text-blue-600">Usługi</a>
            </li>
            <li>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </li>
            <li class="text-gray-900 font-semibold" aria-current="page">{{ $service->name }}</li>
        </ol>
    </nav>

    <article class="bg-white rounded-lg shadow-lg p-8">
        <!-- Featured Image -->
        @if($service->featured_image)
            <img src="{{ Storage::url($service->featured_image) }}"
                 alt="{{ $service->name }}"
                 class="w-full h-96 object-cover rounded-lg mb-6">
        @endif

        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $service->name }}</h1>

            @if($service->excerpt)
                <p class="text-xl text-gray-600 mb-6">{{ $service->excerpt }}</p>
            @endif

            <!-- Service Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 bg-gray-50 rounded-lg mb-6">
                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Czas trwania</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $service->duration_display }}</p>
                </div>

                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Cena</p>
                    @if($service->price_from)
                        <p class="text-lg font-semibold text-gray-900">od {{ number_format($service->price_from, 2) }} PLN</p>
                    @else
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($service->price, 2) }} PLN</p>
                    @endif
                </div>

                <div class="text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Obszar obsługi</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $service->area_served ?? 'Poznań' }}</p>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="mb-6">
                <a href="{{ route('booking.create', $service) }}"
                   class="block w-full md:w-auto md:inline-block text-center bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors duration-300 shadow-lg hover:shadow-xl">
                    Zarezerwuj Termin
                </a>
            </div>
        </header>

        <!-- Main Content (body) -->
        @if($service->body)
            <div class="prose max-w-none mb-8">
                {!! clean($service->body) !!}
            </div>
        @endif

        <!-- Advanced Builder Blocks -->
        @if($service->content)
            @foreach($service->content as $block)
                @if($block['type'] === 'image')
                    <div class="mb-8 @if($block['data']['size'] === 'full') w-full @elseif($block['data']['size'] === 'large') max-w-3xl mx-auto @elseif($block['data']['size'] === 'medium') max-w-2xl mx-auto @else max-w-xl mx-auto @endif">
                        <img src="{{ Storage::url($block['data']['image']) }}"
                             alt="{{ $block['data']['alt'] ?? '' }}"
                             class="w-full rounded-lg">
                        @if(!empty($block['data']['caption']))
                            <p class="text-sm text-gray-600 text-center mt-2">{{ $block['data']['caption'] }}</p>
                        @endif
                    </div>

                @elseif($block['type'] === 'gallery')
                    <div class="mb-8">
                        <div class="grid grid-cols-{{ $block['data']['columns'] ?? 3 }} gap-4">
                            @foreach($block['data']['images'] as $image)
                                <img src="{{ Storage::url($image) }}"
                                     alt=""
                                     class="w-full h-64 object-cover rounded-lg">
                            @endforeach
                        </div>
                    </div>

                @elseif($block['type'] === 'video')
                    <div class="mb-8">
                        @php
                            $videoUrl = $block['data']['url'] ?? '';
                            // Security: Only allow YouTube embed and Vimeo player URLs
                            $isValidVideo = preg_match('%^https://(www\.youtube\.com/embed/|player\.vimeo\.com/video/)%', $videoUrl);
                        @endphp

                        @if($isValidVideo)
                            <div class="aspect-w-16 aspect-h-9">
                                <iframe src="{{ $videoUrl }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                        class="w-full h-96 rounded-lg"></iframe>
                            </div>
                            @if(!empty($block['data']['caption']))
                                <p class="text-sm text-gray-600 text-center mt-2">{{ $block['data']['caption'] }}</p>
                            @endif
                        @else
                            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                                <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-red-700 font-semibold">Nieprawidłowy URL wideo</p>
                                <p class="text-red-600 text-sm mt-2">Dozwolone tylko linki z YouTube (embed) i Vimeo (player)</p>
                            </div>
                        @endif
                    </div>

                @elseif($block['type'] === 'cta')
                    <div class="mb-8 p-8 rounded-lg @if($block['data']['style'] === 'primary') bg-blue-50 @elseif($block['data']['style'] === 'accent') bg-green-50 @else bg-gray-50 @endif">
                        <h3 class="text-2xl font-bold mb-4">{{ $block['data']['heading'] }}</h3>
                        @if(!empty($block['data']['description']))
                            <p class="text-gray-700 mb-6">{{ $block['data']['description'] }}</p>
                        @endif
                        @if(!empty($block['data']['button_url']))
                            <a href="{{ $block['data']['button_url'] }}"
                               class="inline-block px-6 py-3 rounded-lg font-semibold @if($block['data']['style'] === 'primary') bg-blue-600 text-white hover:bg-blue-700 @elseif($block['data']['style'] === 'accent') bg-green-600 text-white hover:bg-green-700 @else bg-gray-600 text-white hover:bg-gray-700 @endif">
                                {{ $block['data']['button_text'] ?? 'Dowiedz się więcej' }}
                            </a>
                        @endif
                    </div>

                @elseif($block['type'] === 'two_columns')
                    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="prose max-w-none">{!! clean($block['data']['left_column']) !!}</div>
                        <div class="prose max-w-none">{!! clean($block['data']['right_column']) !!}</div>
                    </div>

                @elseif($block['type'] === 'three_columns')
                    <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="prose max-w-none">{!! clean($block['data']['column_1']) !!}</div>
                        <div class="prose max-w-none">{!! clean($block['data']['column_2']) !!}</div>
                        <div class="prose max-w-none">{!! clean($block['data']['column_3']) !!}</div>
                    </div>

                @elseif($block['type'] === 'quote')
                    <blockquote class="mb-8 border-l-4 border-blue-600 pl-6 py-4 bg-gray-50 rounded-r-lg">
                        <p class="text-xl text-gray-700 italic mb-4">{{ $block['data']['quote'] }}</p>
                        @if(!empty($block['data']['author']))
                            <footer class="text-gray-600">
                                <strong>{{ $block['data']['author'] }}</strong>
                                @if(!empty($block['data']['author_title']))
                                    <span class="text-gray-500"> - {{ $block['data']['author_title'] }}</span>
                                @endif
                            </footer>
                        @endif
                    </blockquote>
                @endif
            @endforeach
        @endif

        <!-- Related Services -->
        @if($relatedServices->count() > 0)
            <div class="mt-12 pt-8 border-t border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Powiązane usługi</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedServices as $related)
                        <div class="bg-gray-50 rounded-lg p-6 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">
                                <a href="{{ route('service.show', $related) }}" class="hover:text-blue-600">
                                    {{ $related->name }}
                                </a>
                            </h3>
                            @if($related->excerpt)
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $related->excerpt }}</p>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">{{ $related->duration_display }}</span>
                                @if($related->price_from)
                                    <span class="font-semibold text-blue-600">od {{ number_format($related->price_from, 2) }} PLN</span>
                                @else
                                    <span class="font-semibold text-blue-600">{{ number_format($related->price, 2) }} PLN</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Footer CTA -->
        <div class="mt-12 pt-8 border-t border-gray-200 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Gotowy rozpocząć?</h2>
            <p class="text-gray-600 mb-6">Zarezerwuj termin online i doświadcz profesjonalnego detailingu</p>
            <a href="{{ route('booking.create', $service) }}"
               class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300">
                Zarezerwuj Termin
            </a>
        </div>

        <footer class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                Opublikowano: {{ $service->published_at?->format('d.m.Y H:i') }}
            </p>
        </footer>
    </article>
</div>
@endsection
