@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <article class="bg-white rounded-lg shadow-lg overflow-hidden">
        @if($promotion->featured_image)
            <img src="{{ Storage::url($promotion->featured_image) }}"
                 alt="{{ $promotion->title }}"
                 class="w-full h-96 object-cover">
        @endif

        <div class="p-8">
            <header class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-block px-4 py-2 bg-green-100 text-green-800 text-sm font-bold rounded-full">
                        🎉 PROMOCJA
                    </span>

                    @if($promotion->valid_from || $promotion->valid_until)
                        <span class="text-sm text-gray-600">
                            @if($promotion->valid_from && $promotion->valid_until)
                                Ważna: {{ $promotion->valid_from->format('d.m.Y') }} - {{ $promotion->valid_until->format('d.m.Y') }}
                            @elseif($promotion->valid_from)
                                Ważna od: {{ $promotion->valid_from->format('d.m.Y') }}
                            @elseif($promotion->valid_until)
                                Ważna do: {{ $promotion->valid_until->format('d.m.Y') }}
                            @endif
                        </span>
                    @endif
                </div>

                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $promotion->title }}</h1>
            </header>

            @if($promotion->body)
                <div class="prose max-w-none mb-8">
                    {!! $promotion->body !!}
                </div>
            @endif

            @if($promotion->content)
                @foreach($promotion->content as $block)
                    @if($block['type'] === 'image')
                        <div class="mb-8 @if($block['data']['size'] === 'full') w-full @elseif($block['data']['size'] === 'large') max-w-3xl mx-auto @elseif($block['data']['size'] === 'medium') max-w-2xl mx-auto @else max-w-xl mx-auto @endif">
                            <img src="{{ Storage::url($block['data']['image']) }}"
                                 alt="{{ $block['data']['alt'] ?? '' }}"
                                 class="w-full rounded-lg">
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
                                    {{ $block['data']['button_text'] ?? 'Skorzystaj teraz!' }}
                                </a>
                            @endif
                        </div>

                    @elseif($block['type'] === 'two_columns')
                        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="prose max-w-none">{!! $block['data']['left_column'] !!}</div>
                            <div class="prose max-w-none">{!! $block['data']['right_column'] !!}</div>
                        </div>
                    @endif
                @endforeach
            @endif

            <div class="mt-8 pt-6 border-t border-gray-200 bg-green-50 -mx-8 -mb-8 p-8 rounded-b-lg">
                <p class="text-lg font-semibold text-green-800 mb-4">
                    Skorzystaj z tej promocji już dziś!
                </p>
                <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700">
                    Umów wizytę
                </a>
            </div>
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
