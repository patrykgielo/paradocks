@extends('booking-wizard.layout', [
    'currentStep' => 4,
    'nextButtonText' => 'Review Booking',
    'formId' => 'contact-info-form',
    'backUrl' => route('booking.step', ['step' => 3]),
])

@section('step-content')
<div class="contact-info fade-in">
    {{-- Step Title --}}
    <div class="contact-info__header text-center mb-8">
        <h2 class="contact-info__title text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
            Your Contact Information
        </h2>
        <p class="contact-info__subtitle text-lg text-gray-600">
            We'll use this to confirm your appointment and send reminders
        </p>
    </div>

    {{-- Form --}}
    <form
        id="contact-info-form"
        method="POST"
        action="{{ route('booking.step.store', ['step' => 4]) }}"
        class="contact-info__form max-w-2xl mx-auto"
        x-data="contactInfoForm()"
        @submit="validateForm"
    >
        @csrf

        {{-- Section 1: Personal Information --}}
        <div class="contact-info__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Dane Osobowe</h3>

                <div class="space-y-5">
                    {{-- First Name --}}
                    <div class="contact-info__field">
                        <label for="first-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Imię <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="first-name"
                                name="first_name"
                                value="{{ old('first_name', $bookingData['first_name'] ?? '') }}"
                                required
                                autocomplete="given-name"
                                placeholder="Jan"
                                x-model="firstName"
                                @blur="validateField('firstName')"
                                class="contact-info__input w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                :class="validFields.firstName ? 'border-green-500' : ''"
                            >
                            {{-- Validation checkmark --}}
                            <div x-show="validFields.firstName" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        @error('first_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Last Name --}}
                    <div class="contact-info__field">
                        <label for="last-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nazwisko <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="last-name"
                                name="last_name"
                                value="{{ old('last_name', $bookingData['last_name'] ?? '') }}"
                                required
                                autocomplete="family-name"
                                placeholder="Kowalski"
                                x-model="lastName"
                                @blur="validateField('lastName')"
                                class="contact-info__input w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                :class="validFields.lastName ? 'border-green-500' : ''"
                            >
                            <div x-show="validFields.lastName" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        @error('last_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="contact-info__field">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Adres Email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $bookingData['email'] ?? '') }}"
                                required
                                autocomplete="email"
                                placeholder="jan.kowalski@example.com"
                                @if(auth()->check())
                                    readonly
                                @endif
                                x-model="email"
                                @blur="validateField('email')"
                                class="contact-info__input w-full pl-12 pr-12 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200 @if(auth()->check()) bg-gray-50 cursor-not-allowed @endif"
                                :class="validFields.email ? 'border-green-500' : ''"
                            >
                            <div x-show="validFields.email" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        @if(auth()->check())
                            <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Email z Twojego konta nie może być zmieniony podczas rezerwacji
                            </p>
                        @endif
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="contact-info__field">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Numer Telefonu <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="{{ old('phone', $bookingData['phone'] ?? '') }}"
                                required
                                autocomplete="tel"
                                placeholder="+48 123 456 789"
                                x-model="phone"
                                @blur="validateField('phone')"
                                class="contact-info__input w-full pl-12 pr-12 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                :class="validFields.phone ? 'border-green-500' : ''"
                            >
                            <div x-show="validFields.phone" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Format: +48 lub zwykły numer</p>
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Notification Preferences --}}
        <div class="contact-info__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Powiadomienia</h3>
                <p class="text-sm text-gray-600 mb-6">Jak chcesz otrzymywać przypomnienia o rezerwacji?</p>

                <div class="space-y-4">
                    {{-- Email Notifications --}}
                    <label class="contact-info__checkbox-label flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-xl cursor-pointer transition-colors duration-200">
                        <input
                            type="checkbox"
                            name="notify_email"
                            value="1"
                            {{ old('notify_email', session('booking.notify_email', true)) ? 'checked' : '' }}
                            class="contact-info__checkbox mt-1 w-5 h-5 text-orange-500 border-2 border-gray-300 rounded focus:ring-2 focus:ring-orange-200"
                        >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="font-medium text-gray-900">Powiadomienia Email</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Potwierdzenie + przypomnienie 24h przed wizytą</p>
                        </div>
                    </label>

                    {{-- SMS Notifications --}}
                    <label class="contact-info__checkbox-label flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-xl cursor-pointer transition-colors duration-200">
                        <input
                            type="checkbox"
                            name="notify_sms"
                            value="1"
                            {{ old('notify_sms', session('booking.notify_sms', true)) ? 'checked' : '' }}
                            class="contact-info__checkbox mt-1 w-5 h-5 text-orange-500 border-2 border-gray-300 rounded focus:ring-2 focus:ring-orange-200"
                        >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <span class="font-medium text-gray-900">Powiadomienia SMS</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Szybkie przypomnienie 2h przed wizytą</p>
                        </div>
                    </label>

                    {{-- Marketing Consent --}}
                    <label class="contact-info__checkbox-label flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-xl cursor-pointer transition-colors duration-200">
                        <input
                            type="checkbox"
                            name="marketing_consent"
                            value="1"
                            {{ old('marketing_consent', session('booking.marketing_consent')) ? 'checked' : '' }}
                            class="contact-info__checkbox mt-1 w-5 h-5 text-orange-500 border-2 border-gray-300 rounded focus:ring-2 focus:ring-orange-200"
                        >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <span class="font-medium text-gray-900">Promocje i Nowości</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Informacje o rabatach i nowych usługach (opcjonalne)</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 3: Terms & Conditions --}}
        <div class="contact-info__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <label class="contact-info__checkbox-label flex items-start gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="terms_accepted"
                        value="1"
                        required
                        {{ old('terms_accepted') ? 'checked' : '' }}
                        class="contact-info__checkbox mt-1 w-5 h-5 text-orange-500 border-2 border-gray-300 rounded focus:ring-2 focus:ring-orange-200"
                    >
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">
                            Akceptuję
                            <a href="/regulamin" target="_blank" class="text-orange-600 hover:text-orange-700 font-medium underline">Regulamin</a>
                            oraz
                            <a href="/polityka-prywatnosci" target="_blank" class="text-orange-600 hover:text-orange-700 font-medium underline">Politykę Prywatności</a>
                            <span class="text-red-500">*</span>
                        </p>
                    </div>
                </label>
                @error('terms_accepted')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Trust Signal --}}
        <div class="contact-info__trust-signal bg-green-50 rounded-xl p-4 border border-green-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-900">Twoje Dane Są Bezpieczne</div>
                <div class="text-xs text-gray-600">Szyfrowanie SSL · RODO · Nie udostępniamy danych</div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function contactInfoForm() {
    return {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        validFields: {
            firstName: false,
            lastName: false,
            email: false,
            phone: false,
        },

        init() {
            // Pre-validate filled fields on page load
            this.$nextTick(() => {
                if (this.firstName) this.validateField('firstName');
                if (this.lastName) this.validateField('lastName');
                if (this.email) this.validateField('email');
                if (this.phone) this.validateField('phone');
            });
        },

        validateField(fieldName) {
            const value = this[fieldName];

            switch (fieldName) {
                case 'firstName':
                case 'lastName':
                    this.validFields[fieldName] = value.trim().length >= 2;
                    break;

                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    this.validFields[fieldName] = emailRegex.test(value);
                    break;

                case 'phone':
                    // Polish phone: +48123456789 or 123456789
                    const phoneRegex = /^(\+48)?[\s-]?\d{9}$/;
                    this.validFields[fieldName] = phoneRegex.test(value.replace(/\s/g, ''));
                    break;
            }

            // Save progress on valid field
            if (this.validFields[fieldName]) {
                this.saveProgress();
            }
        },

        validateForm(event) {
            // Final validation before submit
            const allValid = Object.values(this.validFields).every(valid => valid);

            if (!allValid) {
                event.preventDefault();
                alert('Proszę poprawnie wypełnić wszystkie wymagane pola.');
                return false;
            }
        },

        saveProgress() {
            // Debounce save to avoid excessive requests
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                fetch('{{ route('booking.save-progress') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        step: 4,
                        data: {
                            first_name: this.firstName,
                            last_name: this.lastName,
                            email: this.email,
                            phone: this.phone,
                        }
                    })
                });
            }, 500);
        }
    }
}
</script>
@endpush

@push('styles')
<style>
/* Contact Info Step */
.contact-info {
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

/* Input Fields */
.contact-info__input {
    transition: all 0.2s ease;
}

.contact-info__input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(249, 115, 22, 0.1);
}

/* Valid field animation */
.contact-info__input.border-green-500 {
    animation: validPulse 0.3s ease;
}

@keyframes validPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
}

/* Checkbox Labels */
.contact-info__checkbox-label {
    transition: all 0.2s ease;
}

.contact-info__checkbox-label:hover {
    transform: translateX(2px);
}

/* Checkbox Styling */
.contact-info__checkbox {
    cursor: pointer;
    transition: all 0.2s ease;
}

.contact-info__checkbox:checked {
    background-color: rgb(249, 115, 22); /* orange-500 */
    border-color: rgb(249, 115, 22);
}

/* Alpine x-cloak */
[x-cloak] {
    display: none !important;
}
</style>
@endpush
