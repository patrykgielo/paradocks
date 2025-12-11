@extends('layouts.app')

@section('title', 'Usługi - Profesjonalny Car Detailing')

@section('content')
{{-- Hero Section --}}
<section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16 md:py-24">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Nasze Usługi
            </h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-8">
                Profesjonalny car detailing w Poznaniu - Przywróć swojemu samochodowi pierwotny wygląd
            </p>
            @auth
                <x-ios.button
                    variant="primary"
                    href="{{ route('booking.step', ['step' => 1]) }}"
                    label="Zarezerwuj Termin"
                    icon="calendar"
                    iconPosition="right"
                    class="bg-white text-blue-600 hover:bg-gray-100"
                />
            @else
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <x-ios.button
                        variant="primary"
                        href="{{ route('register') }}"
                        label="Zarejestruj się"
                        class="bg-white text-blue-600 hover:bg-gray-100"
                    />
                    <x-ios.button
                        variant="ghost"
                        href="{{ route('login') }}"
                        label="Zaloguj się"
                        class="border-2 border-white text-white hover:bg-white hover:text-blue-600"
                    />
                </div>
            @endauth
        </div>
    </div>
</section>

{{-- Services Grid Section --}}
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        @if($services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                @foreach($services as $service)
                    <x-ios.service-card
                        :service="$service"
                        :icon="$service->icon ?? 'sparkles'"
                        class="scroll-reveal"
                    />
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Obecnie brak dostępnych usług</h3>
                <p class="text-gray-600">Wkrótce pojawią się nowe usługi. Sprawdź ponownie później!</p>
            </div>
        @endif
    </div>
</section>

{{-- CTA Section --}}
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-2xl p-8 md:p-12 text-center text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Gotowy na profesjonalny detailing?
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Zarezerwuj termin online i ciesz się czystym autem już dziś
            </p>
            @auth
                <x-ios.button
                    variant="primary"
                    href="{{ route('booking.step', ['step' => 1]) }}"
                    label="Zarezerwuj Termin Teraz"
                    icon="calendar"
                    iconPosition="right"
                    class="bg-white text-blue-600 hover:bg-gray-100"
                />
            @else
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <x-ios.button
                        variant="primary"
                        href="{{ route('register') }}"
                        label="Załóż Konto"
                        class="bg-white text-blue-600 hover:bg-gray-100"
                    />
                    <x-ios.button
                        variant="ghost"
                        href="{{ route('login') }}"
                        label="Mam już konto"
                        class="border-2 border-white text-white hover:bg-white hover:text-blue-600"
                    />
                </div>
            @endauth
        </div>
    </div>
</section>
@endsection
