@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumbs -->
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
            <li class="text-gray-900 font-semibold" aria-current="page">Usługi</li>
        </ol>
    </nav>

    <!-- Header -->
    <header class="mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Nasze Usługi</h1>
        <p class="text-xl text-gray-600">Profesjonalne usługi car detailingu w Poznaniu</p>
    </header>

    <!-- Services Grid -->
    @if($services->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($services as $service)
                <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    @if($service->featured_image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ Storage::url($service->featured_image) }}"
                                 alt="{{ $service->name }}"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif

                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">
                            <a href="{{ route('service.show', $service) }}" class="hover:text-blue-600">
                                {{ $service->name }}
                            </a>
                        </h2>

                        @if($service->excerpt)
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ $service->excerpt }}</p>
                        @endif

                        <div class="flex items-center justify-between mb-4 pt-4 border-t border-gray-200">
                            <div class="text-gray-700">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm">{{ $service->duration_display }}</span>
                            </div>

                            <div class="text-right">
                                @if($service->price_from)
                                    <p class="text-sm text-gray-500">od</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ number_format($service->price_from, 2) }} PLN</p>
                                @else
                                    <p class="text-2xl font-bold text-blue-600">{{ number_format($service->price, 2) }} PLN</p>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('service.show', $service) }}"
                           class="block w-full text-center bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300">
                            Dowiedz się więcej
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="bg-gray-100 rounded-lg p-12 text-center">
            <p class="text-gray-600 text-lg">Obecnie brak dostępnych usług.</p>
        </div>
    @endif

    <!-- CTA Section -->
    <div class="mt-16 bg-blue-50 rounded-lg p-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Gotowy na profesjonalny detailing?</h2>
        <p class="text-gray-700 mb-6">Zarezerwuj termin online i ciesz się czystym autem</p>
        <a href="{{ route('booking.create') }}"
           class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300">
            Zarezerwuj Termin
        </a>
    </div>
</div>
@endsection
