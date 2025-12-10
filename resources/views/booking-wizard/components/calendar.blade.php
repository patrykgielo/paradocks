@props([
    'serviceId' => null,
    'selectedDate' => null,
    'minDate' => 'today',
])

<div class="calendar" x-data="calendarWidget(@js($serviceId), @js($selectedDate))">
    {{-- Hidden input for form submission --}}
    <input
        type="hidden"
        name="date"
        x-model="selectedDate"
        required
    >

    {{-- Calendar container --}}
    <div class="calendar__wrapper">
        <input
            type="text"
            id="booking-date"
            class="calendar__input sr-only"
            placeholder="Wybierz datę"
            x-ref="dateInput"
        >

        {{-- Flatpickr will render inline calendar here --}}
        <div class="calendar__flatpickr"></div>
    </div>

    {{-- Legend (availability indicators) --}}
    <div class="calendar__legend mt-4 flex items-center justify-center gap-6 text-sm text-gray-600">
        <div class="calendar__legend-item flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500"></span>
            <span>Dostępne</span>
        </div>
        <div class="calendar__legend-item flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
            <span>Ograniczone</span>
        </div>
        <div class="calendar__legend-item flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-gray-300"></span>
            <span>Brak</span>
        </div>
    </div>
</div>

@push('scripts')
{{-- Flatpickr library + Polish locale --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pl.js"></script>

<script>
function calendarWidget(serviceId, selectedDate) {
    return {
        selectedDate: selectedDate,
        flatpickrInstance: null,
        unavailableDates: [],
        availabilityData: {},

        init() {
            this.loadUnavailableDates();
            this.initFlatpickr();
        },

        async loadUnavailableDates() {
            if (!serviceId) return;

            try {
                const response = await fetch(`/booking/unavailable-dates?service_id=${serviceId}`);
                const data = await response.json();
                this.unavailableDates = data.unavailable_dates || [];
                this.availabilityData = data.availability || {};

                // Refresh calendar if already initialized
                if (this.flatpickrInstance) {
                    this.flatpickrInstance.set('disable', this.unavailableDates);
                    this.flatpickrInstance.redraw();
                }
            } catch (error) {
                console.error('Failed to load unavailable dates:', error);
            }
        },

        initFlatpickr() {
            this.flatpickrInstance = flatpickr(this.$refs.dateInput, {
                locale: 'pl',
                inline: true, // Embedded calendar (not popup)
                minDate: '{{ $minDate }}',
                dateFormat: 'Y-m-d',
                defaultDate: this.selectedDate,
                disable: this.unavailableDates,

                onChange: (selectedDates, dateStr, instance) => {
                    this.selectedDate = dateStr;

                    // Trigger event for parent component (time grid)
                    this.$dispatch('date-selected', { date: dateStr });

                    // Save to session
                    this.saveProgress(dateStr);
                },

                onDayCreate: (dObj, dStr, fp, dayElem) => {
                    const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                    const availability = this.availabilityData[dateStr] || 'unavailable';

                    // Add availability indicator dot
                    if (availability === 'available') {
                        dayElem.innerHTML += '<span class="availability-dot availability-dot--available"></span>';
                    } else if (availability === 'limited') {
                        dayElem.innerHTML += '<span class="availability-dot availability-dot--limited"></span>';
                    }
                }
            });
        },

        saveProgress(date) {
            fetch('{{ route('booking.save-progress') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    step: 2,
                    data: { date: date }
                })
            });
        }
    }
}
</script>
@endpush

@push('styles')
<style>
/* Flatpickr theme overrides (iOS-style) */
.flatpickr-calendar {
    @apply shadow-lg rounded-2xl border-0 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', sans-serif !important;
}

.flatpickr-months {
    @apply rounded-t-2xl !important;
}

.flatpickr-day {
    @apply w-12 h-12 text-base rounded-lg !important;
    transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
}

.flatpickr-day:hover:not(.flatpickr-disabled) {
    @apply bg-orange-100 !important;
}

.flatpickr-day.selected,
.flatpickr-day.selected:hover {
    @apply bg-orange-500 text-white !important;
    box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.3) !important;
}

.flatpickr-day.today {
    @apply border-2 border-orange-500 !important;
    background: transparent !important;
}

.flatpickr-day.today:not(.selected) {
    @apply text-orange-600 font-bold !important;
}

.flatpickr-day.flatpickr-disabled {
    @apply opacity-30 cursor-not-allowed !important;
    background: #f3f4f6 !important;
}

/* Availability dots */
.availability-dot {
    @apply absolute bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full;
    pointer-events: none;
}

.availability-dot--available {
    @apply bg-green-500;
}

.availability-dot--limited {
    @apply bg-yellow-500;
}

/* Month/year navigation */
.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month {
    @apply rounded-full transition-all duration-200 !important;
}

.flatpickr-months .flatpickr-prev-month:hover,
.flatpickr-months .flatpickr-next-month:hover {
    @apply bg-orange-100 !important;
}

.flatpickr-current-month {
    @apply text-lg font-bold !important;
}

/* Weekday labels */
.flatpickr-weekday {
    @apply text-gray-600 font-medium text-sm !important;
}
</style>
@endpush
