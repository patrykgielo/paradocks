@extends('booking-wizard.layout', [
    'currentStep' => 2,
    'nextButtonText' => 'Continue to Details',
    'formId' => 'datetime-selection-form',
    'backUrl' => route('booking.step', ['step' => 1]),
])

@section('step-content')
<div class="datetime-selection fade-in">
    {{-- Step Title --}}
    <div class="datetime-selection__header text-center mb-8">
        <h2 class="datetime-selection__title text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
            Pick Your Date & Time
        </h2>
        <p class="datetime-selection__subtitle text-lg text-gray-600">
            {{ $service->name }} ({{ $service->duration_minutes }} min)
        </p>
    </div>

    {{-- Form --}}
    <form
        id="datetime-selection-form"
        method="POST"
        action="{{ route('booking.step.store', ['step' => 2]) }}"
        class="datetime-selection__form"
        x-data="{ canSubmit: false }"
        @time-selected.window="canSubmit = true"
    >
        @csrf

        <div class="datetime-selection__grid grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Left Column: Calendar --}}
            <div class="datetime-selection__calendar-section">
                <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Wybierz Datę
                    </h3>

                    <x-booking-wizard.calendar
                        :service-id="$service->id"
                        :selected-date="session('booking.date')"
                        min-date="today"
                    />
                </div>

                {{-- Service Info (mobile: below calendar, desktop: sidebar) --}}
                <div class="datetime-selection__service-info mt-6 bg-orange-50 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-start gap-3">
                        <div class="datetime-selection__service-icon w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">{{ $service->name }}</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ $service->duration_minutes }} minut</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                    <span>Od {{ number_format($service->price_from ?? $service->price, 0, ',', ' ') }} zł</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Time Slots --}}
            <div class="datetime-selection__timeslots-section">
                <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Wybierz Godzinę
                    </h3>

                    <x-booking-wizard.time-grid
                        :date="session('booking.date')"
                        :service-id="$service->id"
                        :selected-time="session('booking.time_slot')"
                        @time-selected.window="canSubmit = true"
                    />
                </div>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="datetime-selection__errors mt-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Proszę popraw następujące błędy:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Trust Signals (below grid) --}}
        <div class="datetime-selection__trust-signals mt-8 pt-8 border-t border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Trust Signal 1: Staff Assignment --}}
                <div class="datetime-selection__trust-item flex items-center gap-3 text-gray-600">
                    <div class="datetime-selection__trust-icon w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-gray-900">Automatyczny Dobór</div>
                        <div class="text-sm">Najlepszy specjalista dla Ciebie</div>
                    </div>
                </div>

                {{-- Trust Signal 2: Booking Count This Week --}}
                @if(($service->booking_count_week ?? 0) > 0)
                    <div class="datetime-selection__trust-item flex items-center gap-3 text-gray-600">
                        <div class="datetime-selection__trust-icon w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ $service->booking_count_week }} Rezerwacji
                            </div>
                            <div class="text-sm">W tym tygodniu</div>
                        </div>
                    </div>
                @else
                    {{-- Fallback: Confirmation Guarantee --}}
                    <div class="datetime-selection__trust-item flex items-center gap-3 text-gray-600">
                        <div class="datetime-selection__trust-icon w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900">Natychmiastowe Potwierdzenie</div>
                            <div class="text-sm">Email + SMS od razu</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Listen for time slot selection to enable submit button
window.addEventListener('time-selected', () => {
    // Enable sticky CTA button in layout
    const submitButton = document.querySelector('button[form="datetime-selection-form"]');
    if (submitButton) {
        submitButton.disabled = false;
    }
});

// Dispatch event when time slot selected (from time-grid component)
document.addEventListener('alpine:initialized', () => {
    // Hook into Alpine component
    const timeGrid = document.querySelector('[x-data*="timeGridWidget"]');
    if (timeGrid) {
        timeGrid.addEventListener('click', (e) => {
            if (e.target.closest('.time-grid__slot:not(:disabled)')) {
                window.dispatchEvent(new CustomEvent('time-selected'));
            }
        });
    }
});
</script>
@endpush
@endsection
