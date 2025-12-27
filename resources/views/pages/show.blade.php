@extends('layouts.app')

@section('content')
@php
    $isHomepage = $page->slug === '/';
@endphp

@if($isHomepage)
    {{-- Homepage: Full-width sections, no container --}}
    @if($page->content)
        @foreach($page->content as $block)
            @if($block['type'] === 'hero')
                <x-content-blocks.hero :data="$block['data']" />

            @elseif($block['type'] === 'content_grid')
                <x-content-blocks.content-grid :data="$block['data']" />

            @elseif($block['type'] === 'feature_list')
                <x-content-blocks.feature-list :data="$block['data']" />

            @elseif($block['type'] === 'cta_banner')
                <x-content-blocks.cta-banner :data="$block['data']" />

            @elseif($block['type'] === 'text_block')
                <x-content-blocks.text-block :data="$block['data']" />

            @elseif($block['type'] === 'custom_html')
                <x-content-blocks.custom-html :data="$block['data']" />
            @endif
        @endforeach
    @endif

    {{-- Homepage body (if exists) in container --}}
    @if($page->body)
        <div class="container mx-auto px-4 py-8">
            <div class="prose max-w-none">
                {!! $page->body !!}
            </div>
        </div>
    @endif

@else
    {{-- Regular pages: Container for content --}}
    <div class="max-w-4xl mx-auto">
        <article class="bg-white rounded-lg shadow-lg p-8">
            <header class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $page->title }}</h1>

                @if($page->featured_image)
                    <img src="{{ Storage::url($page->featured_image) }}"
                         alt="{{ $page->title }}"
                         class="w-full h-96 object-cover rounded-lg mb-6">
                @endif
            </header>

            @if($page->body)
                <div class="prose max-w-none mb-8">
                    {!! $page->body !!}
                </div>
            @endif

            @if($page->content)
                @foreach($page->content as $block)
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
                            <div class="aspect-w-16 aspect-h-9">
                                <iframe src="{{ $block['data']['url'] }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                        class="w-full h-96 rounded-lg"></iframe>
                            </div>
                            @if(!empty($block['data']['caption']))
                                <p class="text-sm text-gray-600 text-center mt-2">{{ $block['data']['caption'] }}</p>
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
                            <div class="prose max-w-none">{!! $block['data']['left_column'] !!}</div>
                            <div class="prose max-w-none">{!! $block['data']['right_column'] !!}</div>
                        </div>

                    @elseif($block['type'] === 'three_columns')
                        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="prose max-w-none">{!! $block['data']['column_1'] !!}</div>
                            <div class="prose max-w-none">{!! $block['data']['column_2'] !!}</div>
                            <div class="prose max-w-none">{!! $block['data']['column_3'] !!}</div>
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

                    @elseif($block['type'] === 'hero')
                        <x-content-blocks.hero :data="$block['data']" />

                    @elseif($block['type'] === 'content_grid')
                        <x-content-blocks.content-grid :data="$block['data']" />

                    @elseif($block['type'] === 'feature_list')
                        <x-content-blocks.feature-list :data="$block['data']" />

                    @elseif($block['type'] === 'cta_banner')
                        <x-content-blocks.cta-banner :data="$block['data']" />

                    @elseif($block['type'] === 'text_block')
                        <x-content-blocks.text-block :data="$block['data']" />

                    @elseif($block['type'] === 'custom_html')
                        <x-content-blocks.custom-html :data="$block['data']" />
                    @endif
                @endforeach
            @endif

            <footer class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    Opublikowano: {{ $page->published_at?->format('d.m.Y H:i') }}
                </p>
            </footer>
        </article>
    </div>
@endif
@endsection
