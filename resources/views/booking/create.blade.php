@extends('layouts.app')

@section('content')
<div class="container-custom max-w-6xl">
    <!-- Multi-Step Booking Wizard with Alpine.js -->
    <div x-data="bookingWizard()"
         x-init="service = {{ json_encode([
             'id' => $service->id,
             'name' => $service->name,
             'description' => $service->description,
             'duration_minutes' => $service->duration_minutes,
             'price' => (float) $service->price
         ]) }}"
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

                        @if($staffMembers->isEmpty())
                            <div class="alert alert-warning">
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <div>
                                        <p class="font-bold">Brak dostępnych pracowników</p>
                                        <p class="mt-1">Obecnie nie ma dostępnych pracowników dla tej usługi. Spróbuj ponownie później.</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Staff Selection -->
                            <div class="mb-6">
                                <label for="staff-select" class="form-label">
                                    Wybierz Specjalistę
                                    <span class="text-red-500">*</span>
                                </label>
                                <select id="staff-select"
                                        @change="
                                            if ($event.target.value) {
                                                staff = JSON.parse($event.target.value);
                                                if (staff && date) fetchAvailableSlots();
                                            } else {
                                                staff = null;
                                            }
                                        "
                                        class="form-input"
                                        :class="{ 'form-input-error': errors.staff }"
                                        required
                                        aria-required="true"
                                        aria-describedby="staff-error">
                                    <option value="">-- Wybierz specjalistę --</option>
                                    @foreach($staffMembers as $staffMember)
                                        <option value="{{ json_encode(['id' => $staffMember->id, 'name' => $staffMember->name]) }}">
                                            {{ $staffMember->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p x-show="errors.staff" class="form-error" id="staff-error" x-text="errors.staff"></p>
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
                                       @change="staff && date ? fetchAvailableSlots() : null"
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="form-input"
                                       :class="{ 'form-input-error': errors.date }"
                                       required
                                       aria-required="true"
                                       aria-describedby="date-error">
                                <p x-show="errors.date" class="form-error" id="date-error" x-text="errors.date"></p>
                            </div>

                            <!-- Available Time Slots -->
                            <div x-show="staff && date" x-transition>
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
                        @endif

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
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Dodatkowe Informacje</h2>

                        <div class="mb-6">
                            <label for="notes-input" class="form-label">Dodatkowe Uwagi (opcjonalnie)</label>
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
                            <button @click="nextStep()" type="button" class="btn btn-primary flex-1">
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
                                    <h3 class="font-bold text-gray-900 mb-2">Specjalista</h3>
                                    <p class="text-gray-700" x-text="staff ? staff.name : '-'"></p>
                                </div>
                                <div class="border-2 border-gray-200 rounded-lg p-4">
                                    <h3 class="font-bold text-gray-900 mb-2">Data</h3>
                                    <p class="text-gray-700" x-text="date || '-'"></p>
                                </div>
                                <div class="border-2 border-gray-200 rounded-lg p-4">
                                    <h3 class="font-bold text-gray-900 mb-2">Godzina</h3>
                                    <p class="text-gray-700" x-text="timeSlot ? `${timeSlot.start} - ${timeSlot.end}` : '-'"></p>
                                </div>
                                <div class="border-2 border-gray-200 rounded-lg p-4">
                                    <h3 class="font-bold text-gray-900 mb-2">Czas trwania</h3>
                                    <p class="text-gray-700" x-text="`${service.duration_minutes} minut`"></p>
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
                            <input type="hidden" name="staff_id" :value="staff ? staff.id : ''">
                            <input type="hidden" name="appointment_date" :value="date">
                            <input type="hidden" name="start_time" :value="timeSlot ? timeSlot.start : ''">
                            <input type="hidden" name="end_time" :value="timeSlot ? timeSlot.end : ''">
                            <input type="hidden" name="notes" :value="customer.notes">

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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <div>
                                    <p class="text-gray-500">Specjalista</p>
                                    <p class="font-medium text-gray-900" x-text="staff ? staff.name : 'Nie wybrano'"></p>
                                </div>
                            </div>

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
@endsection
