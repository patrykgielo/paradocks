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

        {{-- Section 2a: Home Address --}}
        <div class="contact-info__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Adres Zamieszkania</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Twój adres domowy (do celów fakturowania i kontaktu).
                    <span class="text-orange-600 font-medium">Nie musi być taki sam jak miejsce wykonania usługi.</span>
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Street Name --}}
                    <div class="md:col-span-2">
                        <label for="street_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Ulica
                        </label>
                        <input
                            type="text"
                            id="street_name"
                            name="street_name"
                            value="{{ old('street_name', $bookingData['street_name'] ?? '') }}"
                            autocomplete="street-address"
                            placeholder="np. Marszałkowska"
                            x-model="streetName"
                            @input="saveProgress()"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                        @error('street_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Street Number --}}
                    <div>
                        <label for="street_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Numer budynku / lokalu
                        </label>
                        <input
                            type="text"
                            id="street_number"
                            name="street_number"
                            value="{{ old('street_number', $bookingData['street_number'] ?? '') }}"
                            placeholder="np. 12/34"
                            x-model="streetNumber"
                            @input="saveProgress()"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                        @error('street_number')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Postal Code --}}
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Kod pocztowy
                        </label>
                        <input
                            type="text"
                            id="postal_code"
                            name="postal_code"
                            value="{{ old('postal_code', $bookingData['postal_code'] ?? '') }}"
                            autocomplete="postal-code"
                            placeholder="00-000"
                            maxlength="6"
                            x-model="postalCode"
                            @input="formatPostalCode(); saveProgress()"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                        <p class="mt-1 text-xs text-gray-500">Format: XX-XXX</p>
                        @error('postal_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- City --}}
                    <div class="md:col-span-2">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                            Miasto
                        </label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city', $bookingData['city'] ?? '') }}"
                            autocomplete="address-level2"
                            placeholder="np. Warszawa"
                            x-model="city"
                            @input="saveProgress()"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                        @error('city')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2b: Invoice Data (Optional) --}}
        <div class="contact-info__section mb-8" x-data="{ open: {{ old('invoice_requested', session('booking.invoice_requested', false)) ? 'true' : 'false' }} }">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                {{-- Invoice Checkbox --}}
                <label class="contact-info__checkbox-label flex items-start gap-3 cursor-pointer mb-6">
                    <input
                        type="checkbox"
                        name="invoice_requested"
                        value="1"
                        x-model="invoiceRequested"
                        @change="open = invoiceRequested"
                        {{ old('invoice_requested', session('booking.invoice_requested')) ? 'checked' : '' }}
                        class="contact-info__checkbox mt-1 w-5 h-5 text-orange-500 border-2 border-gray-300 rounded focus:ring-2 focus:ring-orange-200"
                    >
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-medium text-gray-900">Chcę otrzymać fakturę VAT</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">Podaj dane do faktury (firmowa lub na osobę prywatną)</p>
                    </div>
                </label>

                {{-- Invoice Fields (Conditional Display) --}}
                <div x-show="open" x-collapse>
                    <div class="space-y-4 pt-4 border-t border-gray-200">

                        {{-- Invoice Type Radio Buttons --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Typ faktury</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="invoice_type"
                                        value="individual"
                                        x-model="invoiceType"
                                        {{ old('invoice_type', session('booking.invoice_type', 'individual')) == 'individual' ? 'checked' : '' }}
                                        class="w-4 h-4 text-orange-500 border-gray-300 focus:ring-orange-200"
                                    >
                                    <span class="text-gray-900">Na osobę prywatną</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="invoice_type"
                                        value="company"
                                        x-model="invoiceType"
                                        {{ old('invoice_type', session('booking.invoice_type')) == 'company' ? 'checked' : '' }}
                                        class="w-4 h-4 text-orange-500 border-gray-300 focus:ring-orange-200"
                                    >
                                    <span class="text-gray-900">Faktura firmowa (z NIP)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="invoice_type"
                                        value="foreign_eu"
                                        x-model="invoiceType"
                                        {{ old('invoice_type', session('booking.invoice_type')) == 'foreign_eu' ? 'checked' : '' }}
                                        class="w-4 h-4 text-orange-500 border-gray-300 focus:ring-orange-200"
                                    >
                                    <span class="text-gray-900">Firma zagraniczna (UE z VAT ID)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="invoice_type"
                                        value="foreign_non_eu"
                                        x-model="invoiceType"
                                        {{ old('invoice_type', session('booking.invoice_type')) == 'foreign_non_eu' ? 'checked' : '' }}
                                        class="w-4 h-4 text-orange-500 border-gray-300 focus:ring-orange-200"
                                    >
                                    <span class="text-gray-900">Firma spoza UE</span>
                                </label>
                            </div>
                            @error('invoice_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Company Name (for company/foreign invoices) --}}
                        <div x-show="['company', 'foreign_eu', 'foreign_non_eu'].includes(invoiceType)">
                            <label for="invoice_company_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nazwa firmy <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="invoice_company_name"
                                name="invoice_company_name"
                                value="{{ old('invoice_company_name', session('booking.invoice_company_name', '')) }}"
                                x-model="invoiceCompanyName"
                                @input="validFields.invoiceCompanyName = invoiceCompanyName.length >= 2"
                                :class="validFields.invoiceCompanyName ? 'border-green-500' : 'border-gray-300'"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:border-orange-500 focus:ring-0 transition-colors"
                                placeholder="np. Paradocks Sp. z o.o."
                            >
                            @error('invoice_company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NIP (Polish company only) --}}
                        <div x-show="invoiceType === 'company'">
                            <label for="invoice_nip" class="block text-sm font-medium text-gray-700 mb-1">
                                NIP <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="invoice_nip"
                                name="invoice_nip"
                                value="{{ old('invoice_nip', session('booking.invoice_nip', '')) }}"
                                x-model="invoiceNIP"
                                @input="formatNIP(); validateNIP()"
                                :class="validFields.invoiceNIP ? 'border-green-500' : 'border-gray-300'"
                                class="w-full px-4 py-3 rounded-xl border-2 focus:border-orange-500 focus:ring-0 transition-colors"
                                placeholder="123-456-78-90"
                                maxlength="13"
                            >
                            <p class="mt-1 text-xs text-gray-500">Format: XXX-XXX-XX-XX (10 cyfr)</p>
                            @error('invoice_nip')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- VAT ID (EU foreign company only) --}}
                        <div x-show="invoiceType === 'foreign_eu'">
                            <label for="invoice_vat_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Numer VAT UE <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="invoice_vat_id"
                                name="invoice_vat_id"
                                value="{{ old('invoice_vat_id', session('booking.invoice_vat_id', '')) }}"
                                x-model="invoiceVATID"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-orange-500 focus:ring-0 transition-colors"
                                placeholder="np. DE123456789"
                            >
                            @error('invoice_vat_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- REGON (optional) --}}
                        <div x-show="invoiceType === 'company'">
                            <label for="invoice_regon" class="block text-sm font-medium text-gray-700 mb-1">
                                REGON <span class="text-gray-400">(opcjonalny)</span>
                            </label>
                            <input
                                type="text"
                                id="invoice_regon"
                                name="invoice_regon"
                                value="{{ old('invoice_regon', session('booking.invoice_regon', '')) }}"
                                x-model="invoiceREGON"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-orange-500 focus:ring-0 transition-colors"
                                placeholder="9 lub 14 cyfr"
                                maxlength="14"
                            >
                            @error('invoice_regon')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Address Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Street --}}
                            <div class="md:col-span-2">
                                <label for="invoice_street" class="block text-sm font-medium text-gray-700 mb-1">
                                    Ulica <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="invoice_street"
                                    name="invoice_street"
                                    value="{{ old('invoice_street', session('booking.invoice_street', '')) }}"
                                    x-model="invoiceStreet"
                                    @input="validFields.invoiceStreet = invoiceStreet.length >= 2"
                                    :class="validFields.invoiceStreet ? 'border-green-500' : 'border-gray-300'"
                                    class="w-full px-4 py-3 rounded-xl border-2 focus:border-orange-500 focus:ring-0 transition-colors"
                                    placeholder="np. Poznańska"
                                >
                                @error('invoice_street')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Street Number --}}
                            <div>
                                <label for="invoice_street_number" class="block text-sm font-medium text-gray-700 mb-1">
                                    Numer
                                </label>
                                <input
                                    type="text"
                                    id="invoice_street_number"
                                    name="invoice_street_number"
                                    value="{{ old('invoice_street_number', session('booking.invoice_street_number', '')) }}"
                                    x-model="invoiceStreetNumber"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-orange-500 focus:ring-0 transition-colors"
                                    placeholder="np. 42A"
                                >
                                @error('invoice_street_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Postal Code --}}
                            <div>
                                <label for="invoice_postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                                    Kod pocztowy <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="invoice_postal_code"
                                    name="invoice_postal_code"
                                    value="{{ old('invoice_postal_code', session('booking.invoice_postal_code', '')) }}"
                                    x-model="invoicePostalCode"
                                    @input="formatInvoicePostalCode(); validFields.invoicePostalCode = /^\d{2}-\d{3}$/.test(invoicePostalCode)"
                                    :class="validFields.invoicePostalCode ? 'border-green-500' : 'border-gray-300'"
                                    class="w-full px-4 py-3 rounded-xl border-2 focus:border-orange-500 focus:ring-0 transition-colors"
                                    placeholder="60-123"
                                    maxlength="6"
                                >
                                @error('invoice_postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div>
                                <label for="invoice_city" class="block text-sm font-medium text-gray-700 mb-1">
                                    Miasto <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="invoice_city"
                                    name="invoice_city"
                                    value="{{ old('invoice_city', session('booking.invoice_city', '')) }}"
                                    x-model="invoiceCity"
                                    @input="validFields.invoiceCity = invoiceCity.length >= 2"
                                    :class="validFields.invoiceCity ? 'border-green-500' : 'border-gray-300'"
                                    class="w-full px-4 py-3 rounded-xl border-2 focus:border-orange-500 focus:ring-0 transition-colors"
                                    placeholder="np. Poznań"
                                >
                                @error('invoice_city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Country --}}
                            <div>
                                <label for="invoice_country" class="block text-sm font-medium text-gray-700 mb-1">
                                    Kraj <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="invoice_country"
                                    name="invoice_country"
                                    x-model="invoiceCountry"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-orange-500 focus:ring-0 transition-colors"
                                >
                                    <option value="PL" {{ old('invoice_country', session('booking.invoice_country', 'PL')) == 'PL' ? 'selected' : '' }}>Polska</option>
                                    <option value="DE" {{ old('invoice_country', session('booking.invoice_country')) == 'DE' ? 'selected' : '' }}>Niemcy</option>
                                    <option value="CZ" {{ old('invoice_country', session('booking.invoice_country')) == 'CZ' ? 'selected' : '' }}>Czechy</option>
                                    <option value="SK" {{ old('invoice_country', session('booking.invoice_country')) == 'SK' ? 'selected' : '' }}>Słowacja</option>
                                    <option value="UA" {{ old('invoice_country', session('booking.invoice_country')) == 'UA' ? 'selected' : '' }}>Ukraina</option>
                                    <option value="GB" {{ old('invoice_country', session('booking.invoice_country')) == 'GB' ? 'selected' : '' }}>Wielka Brytania</option>
                                    <option value="US" {{ old('invoice_country', session('booking.invoice_country')) == 'US' ? 'selected' : '' }}>USA</option>
                                </select>
                                @error('invoice_country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Save Invoice Profile Checkbox (authenticated users only) --}}
                        @auth
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="save_invoice_profile"
                                    value="1"
                                    x-model="saveInvoiceProfile"
                                    {{ old('save_invoice_profile', session('booking.save_invoice_profile')) ? 'checked' : '' }}
                                    class="mt-1 w-4 h-4 text-orange-500 border-2 border-gray-300 rounded focus:ring-2 focus:ring-orange-200"
                                >
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">Zapisz dane do faktury w moim profilu</span>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Dane będą automatycznie wpisywane przy następnych zamówieniach.
                                        Możesz je edytować lub usunąć w ustawieniach konta.
                                    </p>
                                </div>
                            </label>
                        </div>
                        @endauth

                    </div>
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
        // FIXED: Initialize from Blade-rendered values (not empty strings)
        // This reads the actual input value set by Blade template
        firstName: document.getElementById('first-name')?.value || '',
        lastName: document.getElementById('last-name')?.value || '',
        email: document.getElementById('email')?.value || '',
        phone: document.getElementById('phone')?.value || '',

        // Home address fields
        streetName: '{{ old('street_name', session('booking.street_name', '')) }}',
        streetNumber: '{{ old('street_number', session('booking.street_number', '')) }}',
        postalCode: '{{ old('postal_code', session('booking.postal_code', '')) }}',
        city: '{{ old('city', session('booking.city', '')) }}',

        // Invoice fields
        invoiceRequested: {{ old('invoice_requested', session('booking.invoice_requested', false)) ? 'true' : 'false' }},
        invoiceType: '{{ old('invoice_type', session('booking.invoice_type', 'individual')) }}',
        invoiceCompanyName: '{{ old('invoice_company_name', session('booking.invoice_company_name', '')) }}',
        invoiceNIP: '{{ old('invoice_nip', session('booking.invoice_nip', '')) }}',
        invoiceVATID: '{{ old('invoice_vat_id', session('booking.invoice_vat_id', '')) }}',
        invoiceREGON: '{{ old('invoice_regon', session('booking.invoice_regon', '')) }}',
        invoiceStreet: '{{ old('invoice_street', session('booking.invoice_street', '')) }}',
        invoiceStreetNumber: '{{ old('invoice_street_number', session('booking.invoice_street_number', '')) }}',
        invoicePostalCode: '{{ old('invoice_postal_code', session('booking.invoice_postal_code', '')) }}',
        invoiceCity: '{{ old('invoice_city', session('booking.invoice_city', '')) }}',
        invoiceCountry: '{{ old('invoice_country', session('booking.invoice_country', 'PL')) }}',
        saveInvoiceProfile: {{ old('save_invoice_profile', session('booking.save_invoice_profile', false)) ? 'true' : 'false' }},

        validFields: {
            firstName: false,
            lastName: false,
            email: false,
            phone: false,
            invoiceCompanyName: false,
            invoiceNIP: false,
            invoiceStreet: false,
            invoicePostalCode: false,
            invoiceCity: false,
        },

        init() {
            // Pre-validate filled fields on page load
            this.$nextTick(() => {
                if (this.firstName) this.validateField('firstName');
                if (this.lastName) this.validateField('lastName');
                if (this.email) this.validateField('email');
                if (this.phone) this.validateField('phone');

                // Validate invoice fields if present
                if (this.invoiceCompanyName) this.validFields.invoiceCompanyName = this.invoiceCompanyName.length >= 2;
                if (this.invoiceNIP) this.validateNIP();
                if (this.invoiceStreet) this.validFields.invoiceStreet = this.invoiceStreet.length >= 2;
                if (this.invoicePostalCode) this.validFields.invoicePostalCode = /^\d{2}-\d{3}$/.test(this.invoicePostalCode);
                if (this.invoiceCity) this.validFields.invoiceCity = this.invoiceCity.length >= 2;
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
        },

        // Invoice-specific methods
        formatNIP() {
            // Auto-format NIP: XXXXXXXXXX → XXX-XXX-XX-XX
            let nip = this.invoiceNIP.replace(/[^0-9]/g, '');
            if (nip.length === 10) {
                this.invoiceNIP = nip.substring(0, 3) + '-' +
                                  nip.substring(3, 6) + '-' +
                                  nip.substring(6, 8) + '-' +
                                  nip.substring(8, 10);
            }
        },

        validateNIP() {
            const nip = this.invoiceNIP.replace(/[^0-9]/g, '');
            if (nip.length !== 10) {
                this.validFields.invoiceNIP = false;
                return;
            }

            // Weighted checksum (same algorithm as ValidNIP rule)
            const weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                sum += parseInt(nip[i]) * weights[i];
            }
            const checksum = sum % 11;
            this.validFields.invoiceNIP = (checksum !== 10 && checksum === parseInt(nip[9]));
        },

        formatPostalCode() {
            // Auto-format home address postal code: XXXXX → XX-XXX
            let code = this.postalCode.replace(/[^0-9]/g, '');
            if (code.length >= 2) {
                this.postalCode = code.substring(0, 2) + '-' + code.substring(2, 5);
            } else {
                this.postalCode = code;
            }
        },

        formatInvoicePostalCode() {
            // Auto-format invoice postal code: XXXXX → XX-XXX
            let code = this.invoicePostalCode.replace(/[^0-9]/g, '');
            if (code.length >= 5) {
                this.invoicePostalCode = code.substring(0, 2) + '-' + code.substring(2, 5);
            }
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
