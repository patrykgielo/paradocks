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
                <a href="#" class="booking-wizard__help text-sm text-primary-600 hover:text-primary-700 font-medium">
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

{{-- Session Persistence (Laravel Session) + AJAX Form Handler --}}
@push('scripts')
<script>
// Booking Wizard State Management
const bookingWizard = {
    currentStep: {{ $currentStep ?? 1 }},
    isSubmitting: false,

    // Auto-save state to Laravel session via AJAX
    async saveProgress(step, data) {
        try {
            const response = await fetch('{{ route('booking.save-progress') }}', {
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
            const result = await response.json();
            return { response, result };
        } catch (error) {
            console.error('Failed to save progress:', error);
            return { response: { ok: false }, result: { success: false, message: 'Błąd połączenia' } };
        }
    },

    // Navigate to next step (client-side only, no page reload)
    goToStep(step) {
        if (this.isSubmitting) return;
        window.location.href = '{{ route('booking.step', ['step' => '__STEP__']) }}'.replace('__STEP__', step);
    }
};

// Intercept form submissions to use AJAX instead of POST-redirect
document.addEventListener('DOMContentLoaded', () => {
    const wizardForm = document.getElementById('{{ $formId ?? 'booking-form' }}');

    if (wizardForm) {
        wizardForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (bookingWizard.isSubmitting) return;
            bookingWizard.isSubmitting = true;

            const formData = new FormData(wizardForm);
            const data = Object.fromEntries(formData.entries());

            // Get submit button reference outside try block
            const submitBtn = wizardForm.querySelector('button[type="submit"]');
            let originalText = null;

            try {
                if (!submitBtn) {
                    console.error('Submit button not found');
                    return;
                }

                // Show loading state
                originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

                // Save progress via AJAX
                const { response, result } = await bookingWizard.saveProgress(bookingWizard.currentStep, data);

                if (response.ok && result.success !== false) {
                    // Success - navigate to next step WITHOUT page reload warning
                    bookingWizard.goToStep(bookingWizard.currentStep + 1);
                } else {
                    // Validation errors - display them
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (result.errors) {
                        // Show validation errors
                        let errorMessages = Object.values(result.errors).flat().join('\n');
                        alert(result.message + '\n\n' + errorMessages);
                    } else {
                        alert(result.message || 'Wystąpił błąd podczas zapisywania. Spróbuj ponownie.');
                    }
                }
            } catch (error) {
                console.error('Form submission error:', error);

                // Restore button state
                if (submitBtn && originalText) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }

                alert('Wystąpił błąd połączenia. Sprawdź połączenie internetowe i spróbuj ponownie.');
            } finally {
                bookingWizard.isSubmitting = false;
            }
        });
    }
});

// Legacy function for backward compatibility
function saveBookingProgress(step, data) {
    return bookingWizard.saveProgress(step, data);
}
</script>
@endpush
@endsection
