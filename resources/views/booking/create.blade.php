@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">Rezerwacja: {{ $service->name }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Service Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Szczegóły usługi</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-600 text-sm">Usługa</p>
                        <p class="font-semibold">{{ $service->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Czas trwania</p>
                        <p class="font-semibold">{{ $service->duration_minutes }} min</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Cena</p>
                        <p class="font-semibold text-blue-600">{{ number_format($service->price, 2) }} zł</p>
                    </div>
                    @if($service->description)
                        <div>
                            <p class="text-gray-600 text-sm">Opis</p>
                            <p class="text-sm">{{ $service->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-6">Wybierz termin wizyty</h2>

                @if($staffMembers->isEmpty())
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                        <p class="font-bold">Brak dostępnych pracowników</p>
                        <p>Obecnie nie ma dostępnych pracowników dla tej usługi. Spróbuj ponownie później.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('appointments.store') }}" id="booking-form">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        <!-- Staff Selection -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Wybierz pracownika</label>
                            <select name="staff_id" id="staff_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Wybierz pracownika --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Wybierz datę</label>
                            <input type="date" name="appointment_date" id="appointment_date" required
                                   min="{{ now()->format('Y-m-d') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Time Slots -->
                        <div id="time-slots-container" class="mb-6 hidden">
                            <label class="block text-gray-700 font-semibold mb-2">Dostępne godziny</label>
                            <div id="loading" class="text-center py-4 hidden">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <p class="mt-2 text-gray-600">Ładowanie dostępnych terminów...</p>
                            </div>
                            <div id="time-slots" class="grid grid-cols-3 sm:grid-cols-4 gap-2"></div>
                            <div id="no-slots" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded hidden">
                                <p>Brak dostępnych terminów w tym dniu. Wybierz inną datę.</p>
                            </div>
                        </div>

                        <input type="hidden" name="start_time" id="start_time">
                        <input type="hidden" name="end_time" id="end_time">

                        <!-- Notes -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Dodatkowe uwagi (opcjonalnie)</label>
                            <textarea name="notes" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                                      placeholder="Wpisz dodatkowe informacje..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="submit-btn" disabled
                                class="w-full bg-gray-400 text-white py-3 rounded-lg font-semibold cursor-not-allowed">
                            Wybierz termin, aby kontynuować
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$staffMembers->isEmpty())
<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffSelect = document.getElementById('staff_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const timeSlotsDiv = document.getElementById('time-slots');
    const loadingDiv = document.getElementById('loading');
    const noSlotsDiv = document.getElementById('no-slots');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const submitBtn = document.getElementById('submit-btn');

    let selectedSlot = null;

    function loadTimeSlots() {
        const staffId = staffSelect.value;
        const date = dateInput.value;

        if (!staffId || !date) return;

        timeSlotsContainer.classList.remove('hidden');
        loadingDiv.classList.remove('hidden');
        timeSlotsDiv.innerHTML = '';
        noSlotsDiv.classList.add('hidden');
        selectedSlot = null;
        updateSubmitButton();

        fetch('{{ route("booking.slots") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                service_id: {{ $service->id }},
                staff_id: staffId,
                date: date
            })
        })
        .then(response => response.json())
        .then(data => {
            loadingDiv.classList.add('hidden');

            if (data.slots && data.slots.length > 0) {
                data.slots.forEach(slot => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'border-2 border-gray-300 rounded-lg py-2 px-3 text-sm hover:border-blue-500 hover:bg-blue-50 transition duration-200';
                    button.textContent = slot.start;
                    button.dataset.start = slot.start;
                    button.dataset.end = slot.end;

                    button.addEventListener('click', function() {
                        // Remove selection from all buttons
                        document.querySelectorAll('#time-slots button').forEach(btn => {
                            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                            btn.classList.add('border-gray-300');
                        });

                        // Add selection to clicked button
                        this.classList.remove('border-gray-300');
                        this.classList.add('bg-blue-600', 'text-white', 'border-blue-600');

                        startTimeInput.value = this.dataset.start;
                        endTimeInput.value = this.dataset.end;
                        selectedSlot = this.dataset.start;
                        updateSubmitButton();
                    });

                    timeSlotsDiv.appendChild(button);
                });
            } else {
                noSlotsDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            loadingDiv.classList.add('hidden');
            console.error('Error:', error);
            alert('Wystąpił błąd podczas ładowania dostępnych terminów.');
        });
    }

    function updateSubmitButton() {
        if (selectedSlot) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            submitBtn.textContent = 'Zarezerwuj wizytę';
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            submitBtn.textContent = 'Wybierz termin, aby kontynuować';
        }
    }

    staffSelect.addEventListener('change', loadTimeSlots);
    dateInput.addEventListener('change', loadTimeSlots);
});
</script>
@endif
@endsection
