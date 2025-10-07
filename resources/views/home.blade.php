@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-xl p-12 mb-12 text-white">
        <h1 class="text-4xl font-bold mb-4">Witaj w systemie rezerwacji wizyt</h1>
        <p class="text-xl mb-6">Zarezerwuj swoją wizytę online w kilku prostych krokach</p>
        @guest
            <a href="{{ route('register') }}" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 inline-block">
                Rozpocznij teraz
            </a>
        @endguest
    </div>

    <!-- Services Section -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold mb-8 text-gray-800">Nasze usługi</h2>

        @if($services->isEmpty())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                <p class="font-bold">Brak dostępnych usług</p>
                <p>Obecnie nie mamy dostępnych usług. Sprawdź ponownie później.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $service)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2 text-gray-800">{{ $service->name }}</h3>
                            <p class="text-gray-600 mb-4">{{ $service->description }}</p>

                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm text-gray-500">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $service->duration_minutes }} min
                                </div>
                                <div class="text-lg font-bold text-blue-600">
                                    {{ number_format($service->price, 2) }} zł
                                </div>
                            </div>

                            @auth
                                <a href="{{ route('booking.create', $service) }}"
                                   class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    Zarezerwuj wizytę
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="block w-full bg-gray-300 text-gray-700 text-center px-4 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                                    Zaloguj się, aby zarezerwować
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Features Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="text-center">
            <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Łatwa rezerwacja</h3>
            <p class="text-gray-600">Zarezerwuj wizytę w kilku kliknięciach</p>
        </div>

        <div class="text-center">
            <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Natychmiastowe potwierdzenie</h3>
            <p class="text-gray-600">Otrzymasz potwierdzenie od razu</p>
        </div>

        <div class="text-center">
            <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Dostępność 24/7</h3>
            <p class="text-gray-600">Rezerwuj o każdej porze dnia i nocy</p>
        </div>
    </div>
</div>
@endsection
