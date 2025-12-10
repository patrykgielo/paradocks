@extends('booking-wizard.layout', [
    'currentStep' => 5,
    'nextButtonText' => 'Confirm Booking',
    'formId' => 'review-booking-form',
    'backUrl' => route('booking.step', ['step' => 4]),
])

@section('step-content')
<div class="review-booking fade-in">
    {{-- Step Title --}}
    <div class="review-booking__header text-center mb-8">
        <h2 class="review-booking__title text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
            Review Your Booking
        </h2>
        <p class="review-booking__subtitle text-lg text-gray-600">
            Please check all details before confirming your appointment
        </p>
    </div>

    {{-- Form --}}
    <form
        id="review-booking-form"
        method="POST"
        action="{{ route('booking.confirm') }}"
        class="review-booking__form max-w-3xl mx-auto"
    >
        @csrf

        {{-- Section 1: Service Details --}}
        <div class="review-booking__section mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        Usługa
                    </h3>
                    <a
                        href="{{ route('booking.step', ['step' => 1]) }}"
                        class="review-booking__edit-link text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Zmień
                    </a>
                </div>

                <div class="review-booking__detail-row flex items-start gap-4">
                    <div class="flex-1">
                        <div class="text-xl font-bold text-gray-900 mb-2">{{ $service->name }}</div>
                        <div class="text-sm text-gray-600 mb-2">{{ $service->description }}</div>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $service->duration_minutes }} minut
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                                Od {{ number_format($service->price_from ?? $service->price, 0, ',', ' ') }} zł
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Date & Time --}}
        <div class="review-booking__section mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        Data i Godzina
                    </h3>
                    <a
                        href="{{ route('booking.step', ['step' => 2]) }}"
                        class="review-booking__edit-link text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Zmień
                    </a>
                </div>

                <div class="review-booking__detail-row grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <svg class="w-6 h-6 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium">Data</div>
                            <div class="text-base font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse(session('booking.date'))->locale('pl')->isoFormat('dddd, D MMMM YYYY') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <svg class="w-6 h-6 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium">Godzina</div>
                            <div class="text-base font-bold text-gray-900">{{ session('booking.time_slot') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Vehicle & Location --}}
        <div class="review-booking__section mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        Pojazd i Lokalizacja
                    </h3>
                    <a
                        href="{{ route('booking.step', ['step' => 3]) }}"
                        class="review-booking__edit-link text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Zmień
                    </a>
                </div>

                <div class="review-booking__detail-row space-y-4">
                    {{-- Vehicle Type --}}
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium mb-1">Typ Pojazdu</div>
                            <div class="text-base font-bold text-gray-900">
                                {{ $vehicleType->name ?? 'Nie wybrano' }}
                            </div>
                            @if(session('booking.vehicle_brand') || session('booking.vehicle_model'))
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ session('booking.vehicle_brand') }}
                                    @if(session('booking.vehicle_model'))
                                        {{ session('booking.vehicle_model') }}
                                    @endif
                                    @if(session('booking.vehicle_year'))
                                        ({{ session('booking.vehicle_year') }})
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium mb-1">Miejsce Serwisu</div>
                            <div class="text-base font-bold text-gray-900">{{ session('booking.location_address') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Contact Information --}}
        <div class="review-booking__section mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        Dane Kontaktowe
                    </h3>
                    <a
                        href="{{ route('booking.step', ['step' => 4]) }}"
                        class="review-booking__edit-link text-sm font-medium text-orange-600 hover:text-orange-700 flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Zmień
                    </a>
                </div>

                <div class="review-booking__detail-row grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium mb-1">Imię i Nazwisko</div>
                            <div class="text-base font-bold text-gray-900">
                                {{ session('booking.first_name') }} {{ session('booking.last_name') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium mb-1">Telefon</div>
                            <div class="text-base font-bold text-gray-900">{{ session('booking.phone') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 sm:col-span-2">
                        <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <div class="text-xs text-gray-600 font-medium mb-1">Email</div>
                            <div class="text-base font-bold text-gray-900">{{ session('booking.email') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 5: Price Summary --}}
        <div class="review-booking__section mb-8">
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border-2 border-orange-200">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Podsumowanie Ceny</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-gray-900">
                        <span class="text-base">{{ $service->name }}</span>
                        <span class="text-base font-medium">
                            {{ number_format($service->price_from ?? $service->price, 0, ',', ' ') }} zł
                        </span>
                    </div>

                    @if($serviceFee ?? 0 > 0)
                        <div class="flex items-center justify-between text-gray-600 text-sm">
                            <span>Opłata serwisowa</span>
                            <span>{{ number_format($serviceFee, 0, ',', ' ') }} zł</span>
                        </div>
                    @endif

                    <div class="border-t-2 border-orange-200 pt-3 mt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-gray-900">Razem</span>
                            <span class="text-2xl font-bold text-orange-600">
                                {{ number_format(($service->price_from ?? $service->price) + ($serviceFee ?? 0), 0, ',', ' ') }} zł
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-orange-200">
                    <p class="text-xs text-gray-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Darmowa anulacja do 24h przed wizytą
                    </p>
                </div>
            </div>
        </div>

        {{-- Trust Signals --}}
        <div class="review-booking__trust-signals grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-900">Bezpieczna Rezerwacja</div>
                    <div class="text-xs text-gray-600">SSL · Szyfrowanie danych</div>
                </div>
            </div>

            <div class="bg-green-50 rounded-xl p-4 border border-green-200 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-900">Natychmiastowe Potwierdzenie</div>
                    <div class="text-xs text-gray-600">Email + SMS od razu</div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
/* Review Booking Step */
.review-booking {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Edit Links */
.review-booking__edit-link {
    transition: all 0.2s ease;
}

.review-booking__edit-link:hover {
    transform: translateX(2px);
}

/* Detail Sections */
.review-booking__section {
    transition: all 0.3s ease;
}

.review-booking__section:hover {
    transform: translateY(-2px);
}
</style>
@endpush
