@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <article class="bg-white rounded-lg shadow-lg p-8">
        <header class="mb-8">
            @if($post->category)
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full mb-4">
                    {{ $post->category->name }}
                </span>
            @endif

            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>

            <div class="flex items-center text-gray-600 text-sm mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $post->published_at?->format('d.m.Y H:i') }}
            </div>

            @if($post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}"
                     alt="{{ $post->title }}"
                     class="w-full h-96 object-cover rounded-lg mb-6">
            @endif

            @if($post->excerpt)
                <p class="text-xl text-gray-600 mb-6">{{ $post->excerpt }}</p>
            @endif
        </header>

        @if($post->body)
            <div class="prose max-w-none mb-8">
                {!! $post->body !!}
            </div>
        @endif

        @if($post->content)
            @foreach($post->content as $block)
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
    </article>

    <div class="mt-8">
        <a href="{{ route('home') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Powrót do strony głównej
        </a>
    </div>
</div>
@endsection
