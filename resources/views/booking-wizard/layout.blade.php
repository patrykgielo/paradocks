@extends('layouts.app')

@section('content')
<div class="booking-wizard min-h-screen bg-gray-50 pb-32">
    {{-- Header --}}
    <div class="booking-wizard__header bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                {{-- Logo/Back --}}
                <a href="{{ route('home') }}" class="booking-wizard__back-link flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                    <span class="hidden sm:inline">Back to Home</span>
                </a>

                {{-- Title --}}
                <h1 class="booking-wizard__title text-lg sm:text-xl font-bold text-gray-900">
                    Book Your Service
                </h1>

                {{-- Help --}}
                <a href="#" class="booking-wizard__help text-sm text-orange-600 hover:text-orange-700 font-medium">
                    Need Help?
                </a>
            </div>
        </div>
    </div>

    {{-- Progress Indicator --}}
    <x-booking-wizard.progress-indicator
        :current-step="$currentStep ?? 1"
        :total-steps="5"
    />

    {{-- Main Content Area --}}
    <div class="booking-wizard__content container mx-auto px-4 py-8">
        <div class="booking-wizard__container max-w-3xl mx-auto">
            {{-- Step Content (injected by child views) --}}
            @yield('step-content')
        </div>
    </div>

    {{-- Sticky Bottom Actions --}}
    <div class="booking-wizard__actions-sticky fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="booking-wizard__actions flex items-center justify-between gap-4 max-w-3xl mx-auto">
                {{-- Back Button --}}
                @if(($currentStep ?? 1) > 1)
                    <button
                        type="button"
                        class="booking-wizard__back btn btn--secondary flex-shrink-0 flex items-center gap-2"
                        onclick="window.location.href='{{ $backUrl ?? route('booking.step', ['step' => ($currentStep ?? 1) - 1]) }}'"
                    >
                        <x-heroicon-m-arrow-left class="w-5 h-5" />
                        <span class="hidden sm:inline">Back</span>
                    </button>
                @endif

                {{-- Next/Continue Button --}}
                <button
                    type="submit"
                    form="{{ $formId ?? 'booking-form' }}"
                    class="booking-wizard__next btn btn--primary flex-grow flex items-center justify-center gap-2"
                    {{ ($disableNext ?? false) ? 'disabled' : '' }}
                >
                    <span>{{ $nextButtonText ?? 'Continue' }}</span>
                    <x-heroicon-m-arrow-right class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</div>

{{-- iOS Spring Animations --}}
@push('styles')
<style>
/* iOS Spring Animation */
.ios-spring {
    transition-timing-function: cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* Button press feedback (iOS-like) */
.btn:active:not(:disabled) {
    transform: scale(0.95);
    transition: transform 0.1s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Sticky bottom actions safe area (iOS notch) */
@supports (padding: env(safe-area-inset-bottom)) {
    .booking-wizard__actions-sticky {
        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    }
}

/* Touch targets (minimum 48px for iOS) */
.btn {
    min-height: 48px;
}

.btn--primary {
    min-height: 56px; /* Primary CTAs get 56px */
}
</style>
@endpush

{{-- Session Persistence (Laravel Session) --}}
@push('scripts')
<script>
// Auto-save state to Laravel session via AJAX
function saveBookingProgress(step, data) {
    fetch('{{ route('booking.save-progress') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            step: step,
            data: data
        })
    });
}

// Warn before leaving mid-booking
let bookingInProgress = true;
window.addEventListener('beforeunload', (event) => {
    if (bookingInProgress && {{ $currentStep ?? 1 }} > 1) {
        event.preventDefault();
        event.returnValue = '';
        return 'Your booking progress will be lost. Are you sure you want to leave?';
    }
});

// Clear warning after booking confirmed
function bookingConfirmed() {
    bookingInProgress = false;
}
</script>
@endpush
@endsection
