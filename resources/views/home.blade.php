@extends('layouts.app')

@section('content')
@php($marketing = $marketingContent ?? app(\App\Support\Settings\SettingsManager::class)->marketingContent())
<div class="container-custom">
    <!-- Hero Section with Alpine.js -->
    <section class="hero-gradient rounded-2xl shadow-2xl p-8 md:p-12 lg:p-16 mb-16 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(#grid)"/></svg>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 text-black">
                {{ $marketing['hero_title'] ?? 'Profesjonalne Czyszczenie i Detailing Samochodów' }}
            </h1>
            <p class="text-lg md:text-xl mb-8 text-white/90 max-w-2xl mx-auto">
                {{ $marketing['hero_subtitle'] ?? 'Zarezerwuj wizytę online w kilku prostych krokach. Gwarantujemy najwyższą jakość usług i satysfakcję klientów.' }}
            </p>

            <!-- Trust Signals -->
            <div class="flex flex-wrap justify-center gap-4 mb-8">
                <div class="trust-badge" x-data="{ show: false }" x-intersect="show = true" x-show="show" x-transition>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>15+ Lat Doświadczenia</span>
                </div>
                <div class="trust-badge" x-data="{ show: false }" x-intersect.once="show = true" x-show="show" x-transition.delay.100ms>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span>4.9/5 Ocena Klientów</span>
                </div>
                <div class="trust-badge" x-data="{ show: false }" x-intersect.once="show = true" x-show="show" x-transition.delay.200ms>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                    <span>Bezpieczne Płatności</span>
                </div>
            </div>

            @guest
                <a href="{{ route('register') }}" class="btn btn-primary text-lg px-8 py-4 inline-block shadow-xl hover:shadow-2xl">
                    Zarezerwuj Swoją Wizytę
                    <svg class="w-5 h-5 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            @else
                <a href="#services" class="btn btn-primary text-lg text-black px-8 py-4 inline-block shadow-xl hover:shadow-2xl">
                    Zobacz Dostępne Usługi
                    <svg class="w-5 h-5 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
            @endguest
        </div>
    </section>

    <!-- Services Section with Alpine.js -->
    <section id="services" class="section">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $marketing['services_heading'] ?? 'Nasze Usługi Detailingowe' }}</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ $marketing['services_subheading'] ?? 'Wybierz pakiet dopasowany do potrzeb Twojego pojazdu. Wszystkie usługi wykonujemy z najwyższą starannością.' }}
            </p>
        </div>

        @if($services->isEmpty())
            <div class="alert alert-warning max-w-2xl mx-auto">
                <div class="flex items-start">
                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <div>
                        <p class="font-bold">Brak dostępnych usług</p>
                        <p class="mt-1">Obecnie nie mamy dostępnych usług. Sprawdź ponownie później.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"
                 x-data="{
                     selectedService: null,
                     showDetails: false
                 }">
                @foreach($services as $service)
                    <div class="service-card"
                         x-data="serviceCard()"
                         @mouseenter="hover = true"
                         @mouseleave="hover = false"
                         :class="{ 'scale-105': hover }">

                        <!-- Service Image Placeholder -->
                        <div class="relative h-48 bg-gradient-to-br from-primary-100 to-primary-200 overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-20 h-20 text-primary-600 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                            </div>

                            <!-- Quick view badge -->
                            <div class="absolute top-4 right-4">
                                <button @click="toggleDetails()"
                                        class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-lg hover:bg-white transition-all"
                                        :aria-expanded="expanded"
                                        aria-label="Pokaż szczegóły usługi">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $service->name }}</h3>

                            <!-- Service details with expand/collapse -->
                            <div x-show="!expanded">
                                <p class="text-gray-600 mb-4 truncate-2">{{ $service->description ?? 'Profesjonalna usługa detailingowa dla Twojego pojazdu.' }}</p>
                            </div>
                            <div x-show="expanded"
                                 x-transition.duration.300ms
                                 x-cloak>
                                <p class="text-gray-600 mb-4">{{ $service->description ?? 'Profesjonalna usługa detailingowa dla Twojego pojazdu.' }}</p>
                            </div>

                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center text-gray-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">{{ $service->duration_minutes }} min</span>
                                </div>
                                <div class="text-2xl font-bold text-primary-600">
                                    {{ number_format($service->price, 0) }} zł
                                </div>
                            </div>

                            @auth
                                <a href="{{ route('booking.create', $service) }}"
                                   class="btn btn-primary w-full"
                                   aria-label="Zarezerwuj {{ $service->name }}">
                                    Zarezerwuj Termin
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="btn btn-secondary w-full"
                                   aria-label="Zaloguj się, aby zarezerwować {{ $service->name }}">
                                    Zaloguj się, aby zarezerwować
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Features Section -->
    <section class="section bg-gray-50 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8">
        <div class="container-custom">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $marketing['features_heading'] ?? 'Dlaczego Warto Nas Wybrać' }}</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ $marketing['features_subheading'] ?? 'Oferujemy najwyższy standard obsługi i jakości wykonanych usług' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12"
                 x-data="{ visible: false }"
                 x-intersect.once="visible = true">
                @foreach(($marketing['features'] ?? []) as $feature)
                    <div class="text-center"
                         x-show="visible"
                         x-transition.delay.{{ ($loop->index + 1) * 100 }}ms>
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-6 shadow-lg {{ match($loop->index) { 1 => 'bg-accent-100', 2 => 'bg-purple-100', default => 'bg-primary-100' } }}">
                            @switch($loop->index)
                                @case(1)
                                    <svg class="w-8 h-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @break
                                @case(2)
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @break
                                @default
                                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                            @endswitch
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $feature['title'] ?? '' }}</h3>
                        <p class="text-gray-600">{{ $feature['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    @guest
    <section class="section text-center">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                {{ $marketing['cta_heading'] ?? 'Gotowy na Profesjonalny Detailing?' }}
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                {{ $marketing['cta_subheading'] ?? 'Dołącz do setek zadowolonych klientów. Zarejestruj się i zarezerwuj swoją pierwszą wizytę już dziś.' }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="btn btn-primary text-lg">
                    Utwórz Darmowe Konto
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary text-lg">
                    Mam Już Konto
                </a>
            </div>
        </div>
    </section>
    @endguest
</div>

<!-- Mobile Sticky CTA (only for guests on mobile) -->
@guest
<div class="fixed bottom-0 left-0 right-0 p-4 bg-white shadow-2xl lg:hidden z-50"
     x-data="{ show: false }"
     x-intersect.margin.300px.0px.0px.0px="show = false"
     x-intersect:leave="show = true"
     x-show="show"
     x-transition.duration.300ms
     style="display: none;">
    <a href="{{ route('register') }}" class="btn btn-primary w-full text-center">
        Zarezerwuj Wizytę
    </a>
</div>
@endguest

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
