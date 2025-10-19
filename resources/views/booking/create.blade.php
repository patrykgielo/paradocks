@extends('layouts.app')

@section('content')
<div class="container-custom max-w-6xl">
    <!-- Multi-Step Booking Wizard with Alpine.js -->
    <div x-data="bookingWizard()"
         x-init="
             service = {{ json_encode([
                 'id' => $service->id,
                 'name' => $service->name,
                 'description' => $service->description,
                 'duration_minutes' => $service->duration_minutes,
                 'price' => (float) $service->price
             ]) }};
             @auth
             customer.first_name = '{{ $user->first_name ?? '' }}';
             customer.last_name = '{{ $user->last_name ?? '' }}';
             customer.phone_e164 = '{{ $user->phone_e164 ?? '' }}';
             customer.street_name = '{{ $user->street_name ?? '' }}';
             customer.street_number = '{{ $user->street_number ?? '' }}';
             customer.city = '{{ $user->city ?? '' }}';
             customer.postal_code = '{{ $user->postal_code ?? '' }}';
             customer.access_notes = '{{ $user->access_notes ?? '' }}';
             @endauth
         "
         class="mb-12">

        <!-- Progress Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6 text-center">
                Rezerwacja: <span class="text-primary-600" x-text="service.name"></span>
            </h1>

            <!-- Progress Bar -->
            <div class="progress-bar mb-6">
                <div class="progress-bar-fill" :style="`width: ${progressPercentage}%`"></div>
            </div>

            <!-- Step Indicators -->
            <nav aria-label="Kroki rezerwacji" class="flex justify-between items-center max-w-3xl mx-auto">
                <template x-for="(stepName, index) in ['Usługa', 'Termin', 'Dane', 'Podsumowanie']" :key="index">
                    <button @click="goToStep(index + 1)"
                            class="flex flex-col items-center flex-1"
                            :aria-current="step === (index + 1) ? 'step' : undefined"
                            :aria-label="`Krok ${index + 1}: ${stepName}`"
                            type="button">
                        <div class="step-indicator"
                             :class="{
                                 'step-indicator-active': step === (index + 1),
                                 'step-indicator-completed': step > (index + 1)
                             }">
                            <template x-if="step > (index + 1)">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </template>
                            <template x-if="step <= (index + 1)">
                                <span x-text="index + 1"></span>
                            </template>
                        </div>
                        <span class="mt-2 text-xs md:text-sm font-medium text-gray-600 hidden sm:block" x-text="stepName"></span>
                    </button>
                </template>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Step Content -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">

                    <!-- Step 1: Service Confirmation -->
                    <div x-show="step === 1" x-transition.duration.300ms>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Potwierdź Wybraną Usługę</h2>

                        <div class="service-card service-card-selected">
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-3" x-text="service.name"></h3>
                                <p class="text-gray-600 mb-4" x-text="service.description || 'Profesjonalna usługa detailingowa'"></p>

                                <div class="flex items-center justify-between text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-medium" x-text="service.duration_minutes + ' min'"></span>
                                    </div>
                                    <div class="text-2xl font-bold text-primary-600" x-text="Number(service.price).toFixed(0) + ' zł'"></div>
                                </div>
                            </div>
                        </div>

                        <button @click="nextStep()" class="btn btn-primary w-full mt-6">
                            Przejdź Do Wyboru Terminu
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Step 2: Date & Time Selection -->
                    <div x-show="step === 2" x-transition.duration.300ms>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Wybierz Termin Wizyty</h2>

                        <!-- Date Availability Warning -->
                        <div class="alert alert-warning mb-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-bold">Wymaganie 24 godzin</p>
                                    <p class="mt-1">Rezerwacje można składać z co najmniej 24-godzinnym wyprzedzeniem. Najbliższy dostępny termin: <span id="earliest-date" class="font-semibold"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box: Automatic Staff Assignment -->
                        <div class="alert alert-info mb-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                <div>
                                    <p class="font-bold">Automatyczne przypisanie specjalisty</p>
                                    <p class="mt-1">Nasz system automatycznie przypisze dostępnego specjalistę do wybranego terminu.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-6">
                            <label for="date-input" class="form-label">
                                Wybierz Datę
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   id="date-input"
                                   x-model="date"
                                   @change="date ? fetchAvailableSlots() : null"
                                   :min="minDate()"
                                   class="form-input"
                                   :class="{ 'form-input-error': errors.date }"
                                   required
                                   aria-required="true"
                                   aria-describedby="date-error date-help">
                            <p class="form-help" id="date-help">
                                Minimalna rezerwacja: 24 godziny przed wizytą
                            </p>
                            <p x-show="errors.date" class="form-error" id="date-error" x-text="errors.date"></p>
                        </div>

                        <!-- Available Time Slots -->
                        <div x-show="date" x-transition>
                                <label class="form-label">
                                    Dostępne Godziny
                                    <span class="text-red-500">*</span>
                                </label>

                                <!-- Loading State -->
                                <div x-show="loading" class="flex flex-col items-center justify-center py-12">
                                    <div class="spinner"></div>
                                    <p class="mt-4 text-gray-600">Ładowanie dostępnych terminów...</p>
                                </div>

                                <!-- Time Slots Grid -->
                                <div x-show="!loading && availableSlots.length > 0"
                                     class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
                                     role="radiogroup"
                                     aria-label="Dostępne godziny">
                                    <template x-for="slot in availableSlots" :key="slot.start">
                                        <button type="button"
                                                @click="selectTimeSlot(slot)"
                                                class="time-slot"
                                                :class="{ 'time-slot-selected': timeSlot && timeSlot.start === slot.start }"
                                                :aria-checked="timeSlot && timeSlot.start === slot.start"
                                                role="radio">
                                            <span class="block text-lg font-semibold" x-text="slot.start"></span>
                                            <span class="block text-xs text-gray-500" x-text="`do ${slot.end}`"></span>
                                        </button>
                                    </template>
                                </div>

                                <!-- No Slots Available -->
                                <div x-show="!loading && availableSlots.length === 0" class="alert alert-warning">
                                    <p>Brak dostępnych terminów w tym dniu. Wybierz inną datę.</p>
                                </div>

                                <!-- Error Message -->
                                <div x-show="errors.slots" class="alert alert-error mt-4">
                                    <p x-text="errors.slots"></p>
                                </div>

                                <p x-show="errors.timeSlot" class="form-error mt-2" x-text="errors.timeSlot"></p>
                            </div>

                        <!-- Navigation Buttons -->
                        <div class="flex gap-4 mt-8">
                            <button @click="prevStep()" type="button" class="btn btn-ghost flex-1">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                                </svg>
                                Wstecz
                            </button>
                            <button @click="nextStep()"
                                    type="button"
                                    :disabled="!timeSlot"
                                    class="btn btn-primary flex-1">
                                Dalej
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Customer Details -->
                    <div x-show="step === 3" x-transition.duration.300ms>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Twoje Dane Kontaktowe</h2>

                        <!-- Personal Data Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dane Osobowe</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="form-label">
                                        Imię
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="first_name"
                                           x-model="customer.first_name"
                                           class="form-input"
                                           :class="{ 'form-input-error': errors.first_name }"
                                           placeholder="Jan"
                                           required
                                           maxlength="255">
                                    <p x-show="errors.first_name" class="form-error" x-text="errors.first_name"></p>
                                </div>
                                <div>
                                    <label for="last_name" class="form-label">
                                        Nazwisko
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="last_name"
                                           x-model="customer.last_name"
                                           class="form-input"
                                           :class="{ 'form-input-error': errors.last_name }"
                                           placeholder="Kowalski"
                                           required
                                           maxlength="255">
                                    <p x-show="errors.last_name" class="form-error" x-text="errors.last_name"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontakt</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="phone_e164" class="form-label">
                                        Telefon
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel"
                                           id="phone_e164"
                                           x-model="customer.phone_e164"
                                           class="form-input"
                                           :class="{ 'form-input-error': errors.phone_e164 }"
                                           placeholder="+48501234567"
                                           required
                                           maxlength="20">
                                    <p class="form-help">Format międzynarodowy, np. +48501234567</p>
                                    <p x-show="errors.phone_e164" class="form-error" x-text="errors.phone_e164"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Adres (opcjonalnie)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="street_name" class="form-label">Ulica</label>
                                    <input type="text"
                                           id="street_name"
                                           x-model="customer.street_name"
                                           class="form-input"
                                           placeholder="Marszałkowska"
                                           maxlength="255">
                                </div>
                                <div>
                                    <label for="street_number" class="form-label">Numer</label>
                                    <input type="text"
                                           id="street_number"
                                           x-model="customer.street_number"
                                           class="form-input"
                                           placeholder="12/34"
                                           maxlength="20">
                                </div>
                                <div>
                                    <label for="city" class="form-label">Miasto</label>
                                    <input type="text"
                                           id="city"
                                           x-model="customer.city"
                                           class="form-input"
                                           placeholder="Warszawa"
                                           maxlength="255">
                                </div>
                                <div>
                                    <label for="postal_code" class="form-label">Kod pocztowy</label>
                                    <input type="text"
                                           id="postal_code"
                                           x-model="customer.postal_code"
                                           class="form-input"
                                           placeholder="00-000"
                                           maxlength="10"
                                           x-mask="99-999">
                                    <p x-show="errors.postal_code" class="form-error" x-text="errors.postal_code"></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="access_notes" class="form-label">Informacje o dostępie</label>
                                <textarea id="access_notes"
                                          x-model="customer.access_notes"
                                          rows="3"
                                          class="form-input"
                                          maxlength="1000"
                                          placeholder="Dodatkowe informacje o adresie, np. kod do bramy, piętro..."></textarea>
                            </div>
                        </div>

                        <!-- Additional Notes Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Dodatkowe Uwagi</h3>
                            <div>
                                <label for="notes-input" class="form-label">Uwagi do wizyty (opcjonalnie)</label>
                                <textarea id="notes-input"
                                          x-model="customer.notes"
                                          rows="4"
                                          class="form-input"
                                          maxlength="1000"
                                          placeholder="Wpisz dodatkowe informacje, np. specjalne życzenia, informacje o pojeździe..."
                                          aria-describedby="notes-help"></textarea>
                                <p class="form-help" id="notes-help">
                                    Możesz dodać informacje, które pomogą nam lepiej przygotować się do usługi.
                                </p>
                            </div>
                        </div>

                        <!-- Important Information Box -->
                        <div class="alert alert-info">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                <div>
                                    <p class="font-bold mb-1">Ważne Informacje</p>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        <li>Prosimy o przybycie 5 minut przed umówionym terminem</li>
                                        <li>W przypadku spóźnienia powyżej 15 minut rezerwacja może zostać anulowana</li>
                                        <li>Możesz anulować wizytę do 24 godzin przed terminem</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex gap-4 mt-8">
                            <button @click="prevStep()" type="button" class="btn btn-ghost flex-1">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                                </svg>
                                Wstecz
                            </button>
                            <button @click="validateStep3() && nextStep()" type="button" class="btn btn-primary flex-1">
                                Podsumowanie
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Summary & Confirmation -->
                    <div x-show="step === 4" x-transition.duration.300ms>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Podsumowanie Rezerwacji</h2>

                        <div class="space-y-6">
                            <!-- Service Summary -->
                            <div class="border-2 border-primary-100 rounded-lg p-4 bg-primary-50/50">
                                <h3 class="font-bold text-gray-900 mb-2">Usługa</h3>
                                <p class="text-lg text-gray-700" x-text="service.name"></p>
                            </div>

                            <!-- Appointment Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border-2 border-gray-200 rounded-lg p-4">
                                    <h3 class="font-bold text-gray-900 mb-2">Data</h3>
                                    <p class="text-gray-700" x-text="date || '-'"></p>
                                </div>
                                <div class="border-2 border-gray-200 rounded-lg p-4">
                                    <h3 class="font-bold text-gray-900 mb-2">Godzina</h3>
                                    <p class="text-gray-700" x-text="timeSlot ? `${timeSlot.start} - ${timeSlot.end}` : '-'"></p>
                                </div>
                                <div class="border-2 border-gray-200 rounded-lg p-4 md:col-span-2">
                                    <h3 class="font-bold text-gray-900 mb-2">Czas trwania</h3>
                                    <p class="text-gray-700" x-text="`${service.duration_minutes} minut`"></p>
                                </div>
                            </div>

                            <!-- Customer Details Summary -->
                            <div class="border-2 border-gray-200 rounded-lg p-4">
                                <h3 class="font-bold text-gray-900 mb-3">Twoje Dane</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-500">Imię i nazwisko:</span>
                                        <span class="font-medium ml-2" x-text="`${customer.first_name} ${customer.last_name}`"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Telefon:</span>
                                        <span class="font-medium ml-2" x-text="customer.phone_e164"></span>
                                    </div>
                                    <div x-show="customer.city" class="md:col-span-2">
                                        <span class="text-gray-500">Adres:</span>
                                        <span class="font-medium ml-2" x-text="`${customer.street_name || ''} ${customer.street_number || ''}, ${customer.postal_code || ''} ${customer.city || ''}`"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Summary -->
                            <div x-show="customer.notes" class="border-2 border-gray-200 rounded-lg p-4">
                                <h3 class="font-bold text-gray-900 mb-2">Uwagi</h3>
                                <p class="text-gray-700" x-text="customer.notes"></p>
                            </div>

                            <!-- Price Summary -->
                            <div class="bg-gradient-to-r from-primary-50 to-accent-50 rounded-lg p-6 border-2 border-primary-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Całkowity Koszt:</span>
                                    <span class="text-3xl font-bold text-primary-600" x-text="Number(service.price).toFixed(0) + ' zł'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Final Form Submission -->
                        <form method="POST" action="{{ route('appointments.store') }}" class="mt-8">
                            @csrf
                            <input type="hidden" name="service_id" :value="service.id">
                            <!-- staff_id is auto-assigned by backend -->
                            <input type="hidden" name="appointment_date" :value="date">
                            <input type="hidden" name="start_time" :value="timeSlot ? timeSlot.start : ''">
                            <input type="hidden" name="end_time" :value="timeSlot ? timeSlot.end : ''">
                            <input type="hidden" name="notes" :value="customer.notes">
                            <!-- New profile fields -->
                            <input type="hidden" name="first_name" :value="customer.first_name">
                            <input type="hidden" name="last_name" :value="customer.last_name">
                            <input type="hidden" name="phone_e164" :value="customer.phone_e164">
                            <input type="hidden" name="street_name" :value="customer.street_name">
                            <input type="hidden" name="street_number" :value="customer.street_number">
                            <input type="hidden" name="city" :value="customer.city">
                            <input type="hidden" name="postal_code" :value="customer.postal_code">
                            <input type="hidden" name="access_notes" :value="customer.access_notes">

                            <!-- Navigation Buttons -->
                            <div class="flex gap-4">
                                <button @click="prevStep()" type="button" class="btn btn-ghost flex-1">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                                    </svg>
                                    Wstecz
                                </button>
                                <button type="submit" class="btn btn-primary flex-1 text-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Potwierdź Rezerwację
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Summary (Sticky) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Twoja Rezerwacja</h3>

                    <div class="space-y-4">
                        <!-- Service Info -->
                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 mb-1">Usługa</p>
                            <p class="font-semibold text-gray-900" x-text="service.name"></p>
                        </div>

                        <!-- Selected Details -->
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <p class="text-gray-500">Data</p>
                                    <p class="font-medium text-gray-900" x-text="date || 'Nie wybrano'"></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-gray-500">Godzina</p>
                                    <p class="font-medium text-gray-900" x-text="timeSlot ? `${timeSlot.start} - ${timeSlot.end}` : 'Nie wybrano'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-900">Cena:</span>
                                <span class="text-2xl font-bold text-primary-600" x-text="Number(service.price).toFixed(0) + ' zł'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
    // Calculate and display earliest available date
    document.addEventListener('DOMContentLoaded', function() {
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);
        minDate.setHours(0, 0, 0, 0);

        const formatted = minDate.toLocaleDateString('pl-PL', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const el = document.getElementById('earliest-date');
        if (el) el.textContent = formatted;
    });
</script>
@endsection
