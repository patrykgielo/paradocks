@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <article class="bg-white rounded-lg shadow-lg p-8">
        <header class="mb-8">
            @if($portfolioItem->category)
                <span class="inline-block px-3 py-1 bg-purple-100 text-purple-800 text-sm font-semibold rounded-full mb-4">
                    {{ $portfolioItem->category->name }}
                </span>
            @endif

            <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ $portfolioItem->title }}</h1>
        </header>

        @if($portfolioItem->before_image || $portfolioItem->after_image)
            <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($portfolioItem->before_image)
                    <div class="relative">
                        <span class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-lg font-semibold z-10">
                            PRZED
                        </span>
                        <img src="{{ Storage::url($portfolioItem->before_image) }}"
                             alt="{{ $portfolioItem->title }} - Przed"
                             class="w-full h-96 object-cover rounded-lg">
                    </div>
                @endif

                @if($portfolioItem->after_image)
                    <div class="relative">
                        <span class="absolute top-4 left-4 bg-green-600 text-white px-3 py-1 rounded-lg font-semibold z-10">
                            PO
                        </span>
                        <img src="{{ Storage::url($portfolioItem->after_image) }}"
                             alt="{{ $portfolioItem->title }} - Po"
                             class="w-full h-96 object-cover rounded-lg">
                    </div>
                @endif
            </div>
        @endif

        @if($portfolioItem->body)
            <div class="prose max-w-none mb-8">
                {!! $portfolioItem->body !!}
            </div>
        @endif

        @if($portfolioItem->gallery && count($portfolioItem->gallery) > 0)
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Galeria zdjęć</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($portfolioItem->gallery as $image)
                        <img src="{{ Storage::url($image) }}"
                             alt="{{ $portfolioItem->title }}"
                             class="w-full h-64 object-cover rounded-lg hover:scale-105 transition-transform cursor-pointer">
                    @endforeach
                </div>
            </div>
        @endif

        @if($portfolioItem->content)
            @foreach($portfolioItem->content as $block)
                @if($block['type'] === 'quote')
                    <blockquote class="mb-8 border-l-4 border-purple-600 pl-6 py-4 bg-purple-50 rounded-r-lg">
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

        <footer class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Opublikowano: {{ $portfolioItem->published_at?->format('d.m.Y') }}
                </p>
                @if($portfolioItem->category)
                    <p class="text-sm text-gray-600">
                        Kategoria: <strong>{{ $portfolioItem->category->name }}</strong>
                    </p>
                @endif
            </div>
        </footer>

        <div class="mt-8 p-6 bg-blue-50 rounded-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Chcesz podobny efekt?</h3>
            <p class="text-gray-700 mb-4">
                Skontaktuj się z nami i umów wizytę. Nasi specjaliści pomogą Ci uzyskać wymarzone rezultaty!
            </p>
            <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">
                Umów wizytę
            </a>
        </div>
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
