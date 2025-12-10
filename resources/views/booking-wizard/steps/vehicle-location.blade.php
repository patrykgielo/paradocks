@extends('booking-wizard.layout', [
    'currentStep' => 3,
    'nextButtonText' => 'Continue to Contact Info',
    'formId' => 'vehicle-location-form',
    'backUrl' => route('booking.step', ['step' => 2]),
])

@section('step-content')
<div class="vehicle-location fade-in">
    {{-- Step Title --}}
    <div class="vehicle-location__header text-center mb-8">
        <h2 class="vehicle-location__title text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
            Vehicle & Location Details
        </h2>
        <p class="vehicle-location__subtitle text-lg text-gray-600">
            Tell us about your vehicle and where we should meet you
        </p>
    </div>

    {{-- Form --}}
    <form
        id="vehicle-location-form"
        method="POST"
        action="{{ route('booking.step.store', ['step' => 3]) }}"
        class="vehicle-location__form max-w-2xl mx-auto"
        x-data="vehicleLocationForm(@js(session('booking.vehicle_type_id')), @js(session('booking.location_address')))"
    >
        @csrf

        {{-- Section 1: Vehicle Type (Required) --}}
        <div class="vehicle-location__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Typ Pojazdu</h3>
                        <p class="text-sm text-gray-600">Wybierz rozmiar swojego auta</p>
                    </div>
                    <span class="text-xs font-semibold text-orange-600 bg-orange-100 px-3 py-1 rounded-full">
                        Wymagane
                    </span>
                </div>

                {{-- Vehicle Type Selection Button --}}
                <button
                    type="button"
                    @click="$dispatch('open-bottom-sheet', { id: 'vehicle-type-selector' })"
                    class="vehicle-location__type-selector w-full flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 active:scale-98 border-2 border-gray-300 rounded-xl transition-all duration-200 text-left"
                    :class="selectedVehicleType ? 'border-orange-500 bg-orange-50' : ''"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-0.5">Wybrany typ pojazdu</div>
                            <div class="text-base font-bold text-gray-900" x-text="selectedVehicleTypeName || 'Wybierz typ pojazdu'"></div>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                {{-- Hidden input for form submission --}}
                <input
                    type="hidden"
                    name="vehicle_type_id"
                    x-model="selectedVehicleType"
                    required
                >

                @error('vehicle_type_id')
                    <p class="mt-2 text-sm text-red-600 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- Section 2: Vehicle Details (Optional) --}}
        <div class="vehicle-location__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Szczegóły Pojazdu</h3>
                        <p class="text-sm text-gray-600">Opcjonalnie - pomaga nam się lepiej przygotować</p>
                    </div>
                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        Opcjonalne
                    </span>
                </div>

                <div class="space-y-4">
                    {{-- Brand --}}
                    <div class="vehicle-location__field">
                        <label for="vehicle-brand" class="block text-sm font-medium text-gray-700 mb-2">
                            Marka
                        </label>
                        <input
                            type="text"
                            id="vehicle-brand"
                            name="vehicle_brand"
                            value="{{ old('vehicle_brand', session('booking.vehicle_brand')) }}"
                            placeholder="np. BMW, Toyota, Volkswagen"
                            class="vehicle-location__input w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                    </div>

                    {{-- Model --}}
                    <div class="vehicle-location__field">
                        <label for="vehicle-model" class="block text-sm font-medium text-gray-700 mb-2">
                            Model
                        </label>
                        <input
                            type="text"
                            id="vehicle-model"
                            name="vehicle_model"
                            value="{{ old('vehicle_model', session('booking.vehicle_model')) }}"
                            placeholder="np. 320d, Corolla, Golf"
                            class="vehicle-location__input w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                    </div>

                    {{-- Year --}}
                    <div class="vehicle-location__field">
                        <label for="vehicle-year" class="block text-sm font-medium text-gray-700 mb-2">
                            Rok produkcji
                        </label>
                        <input
                            type="number"
                            id="vehicle-year"
                            name="vehicle_year"
                            value="{{ old('vehicle_year', session('booking.vehicle_year')) }}"
                            placeholder="np. 2020"
                            min="1900"
                            max="{{ date('Y') + 1 }}"
                            class="vehicle-location__input w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Service Location (Required) --}}
        <div class="vehicle-location__section mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Miejsce Serwisu</h3>
                        <p class="text-sm text-gray-600">Gdzie możemy przyjechać z samochodem?</p>
                    </div>
                    <span class="text-xs font-semibold text-orange-600 bg-orange-100 px-3 py-1 rounded-full">
                        Wymagane
                    </span>
                </div>

                {{-- Address Input with Google Maps Autocomplete --}}
                <div class="vehicle-location__field">
                    <label for="location-address" class="block text-sm font-medium text-gray-700 mb-2">
                        Adres
                    </label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="location-address"
                            name="location_address"
                            x-model="locationAddress"
                            placeholder="Zacznij wpisywać adres..."
                            required
                            class="vehicle-location__input w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                        >
                    </div>

                    {{-- Hidden fields for location data --}}
                    <input type="hidden" id="location-latitude" name="location_latitude" x-model="locationLat">
                    <input type="hidden" id="location-longitude" name="location_longitude" x-model="locationLng">
                    <input type="hidden" id="location-place-id" name="location_place_id" x-model="locationPlaceId">
                    <input type="hidden" id="location-components" name="location_components" x-model="locationComponents">

                    @error('location_address')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Map Preview (if location selected) --}}
                <div class="vehicle-location__map-preview mt-4" x-show="locationLat && locationLng" x-cloak>
                    <div id="location-map" class="w-full h-64 rounded-xl overflow-hidden border-2 border-gray-200"></div>
                </div>
            </div>
        </div>

        {{-- Trust Signal --}}
        <div class="vehicle-location__trust-signal bg-blue-50 rounded-xl p-4 border border-blue-200 flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-900">Bezpieczna Lokalizacja</div>
                <div class="text-xs text-gray-600">Twój adres nie jest udostępniany publicznie</div>
            </div>
        </div>
    </form>
</div>

{{-- Bottom Sheet: Vehicle Type Selector --}}
<x-booking-wizard.bottom-sheet id="vehicle-type-selector" title="Wybierz Typ Pojazdu">
    <div class="vehicle-type-cards grid grid-cols-1 gap-4">
        @foreach($vehicleTypes as $type)
            <button
                type="button"
                @click="selectVehicleType({{ $type->id }}, '{{ $type->name }}')"
                class="vehicle-type-card text-left p-4 bg-white hover:bg-orange-50 active:scale-98 border-2 border-gray-200 hover:border-orange-500 rounded-xl transition-all duration-200"
                :class="selectedVehicleType === {{ $type->id }} ? 'border-orange-500 bg-orange-50 ring-4 ring-orange-200' : ''"
            >
                <div class="flex items-start gap-4">
                    {{-- Icon --}}
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1">
                        <h4 class="text-base font-bold text-gray-900 mb-1">{{ $type->name }}</h4>
                        <p class="text-sm text-gray-600 mb-2">{{ $type->description }}</p>
                        <div class="text-xs text-gray-500 italic">
                            Przykłady: {{ $type->examples }}
                        </div>
                    </div>

                    {{-- Selection Indicator --}}
                    <div x-show="selectedVehicleType === {{ $type->id }}" x-cloak class="flex-shrink-0">
                        <div class="w-6 h-6 rounded-full bg-orange-500 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </button>
        @endforeach
    </div>
</x-booking-wizard.bottom-sheet>
@endsection

@push('scripts')
{{-- Google Maps API --}}
@if($googleMapsApiKey)
<script>
    window.GOOGLE_MAPS_CONFIG = {
        apiKey: '{{ $googleMapsApiKey }}',
        mapId: '{{ $googleMapsMapId }}'
    };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initGoogleMaps" async defer></script>
@endif

<script>
let mapInstance = null;
let markerInstance = null;

function vehicleLocationForm(initialVehicleType, initialAddress) {
    return {
        selectedVehicleType: initialVehicleType,
        selectedVehicleTypeName: '',
        locationAddress: initialAddress || '',
        locationLat: '',
        locationLng: '',
        locationPlaceId: '',
        locationComponents: '',

        init() {
            // Load vehicle type name if pre-selected
            if (this.selectedVehicleType) {
                const typeButton = document.querySelector(`button[onclick*="selectVehicleType(${this.selectedVehicleType}"]`);
                if (typeButton) {
                    const typeName = typeButton.querySelector('h4')?.textContent;
                    if (typeName) {
                        this.selectedVehicleTypeName = typeName;
                    }
                }
            }
        },

        selectVehicleType(typeId, typeName) {
            this.selectedVehicleType = typeId;
            this.selectedVehicleTypeName = typeName;

            // Close bottom sheet
            this.$dispatch('close-bottom-sheet', { id: 'vehicle-type-selector' });

            // Haptic feedback
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate(10);
            }

            // Save progress
            this.saveProgress();
        }
    }
}

function initGoogleMaps() {
    const addressInput = document.getElementById('location-address');
    if (!addressInput) return;

    // Initialize autocomplete
    const autocomplete = new google.maps.places.Autocomplete(addressInput, {
        types: ['address'],
        componentRestrictions: { country: 'pl' }
    });

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();

        if (place.geometry) {
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();

            // Update Alpine.js data
            const alpineComponent = Alpine.$data(addressInput.closest('form'));
            if (alpineComponent) {
                alpineComponent.locationLat = lat;
                alpineComponent.locationLng = lng;
                alpineComponent.locationPlaceId = place.place_id || '';
                alpineComponent.locationComponents = JSON.stringify(place.address_components || []);
                alpineComponent.locationAddress = place.formatted_address || addressInput.value;
            }

            // Initialize or update map
            setTimeout(() => initMap(lat, lng), 100);
        }
    });
}

function initMap(lat, lng) {
    const mapContainer = document.getElementById('location-map');
    if (!mapContainer) return;

    const position = { lat, lng };

    if (!mapInstance) {
        mapInstance = new google.maps.Map(mapContainer, {
            center: position,
            zoom: 16,
            mapId: window.GOOGLE_MAPS_CONFIG.mapId,
            disableDefaultUI: true,
            zoomControl: true,
        });
    } else {
        mapInstance.setCenter(position);
    }

    // Add or update marker
    if (markerInstance) {
        markerInstance.setMap(null);
    }

    markerInstance = new google.maps.marker.AdvancedMarkerElement({
        map: mapInstance,
        position: position,
        title: 'Lokalizacja serwisu',
    });
}
</script>
@endpush

@push('styles')
<style>
/* Vehicle Location Step */
.vehicle-location {
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

/* Vehicle Type Selector Button */
.vehicle-location__type-selector {
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.vehicle-location__type-selector:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.vehicle-location__type-selector:active {
    transform: scale(0.98);
}

/* Input Fields */
.vehicle-location__input {
    transition: all 0.2s ease;
}

.vehicle-location__input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(249, 115, 22, 0.1);
}

/* Vehicle Type Cards in Bottom Sheet */
.vehicle-type-card {
    transition: all 0.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.vehicle-type-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.vehicle-type-card:active {
    transform: scale(0.98);
}

/* Alpine x-cloak */
[x-cloak] {
    display: none !important;
}

/* Active state scale */
.active\:scale-98:active {
    transform: scale(0.98);
}
</style>
@endpush
