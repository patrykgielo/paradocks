<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Dane osobowe') }}</h2>

    <form action="{{ route('profile.personal.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- First Name --}}
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Imię') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="first_name" id="first_name"
                       value="{{ old('first_name', $user->first_name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       required>
                @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Last Name --}}
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Nazwisko') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="last_name" id="last_name"
                       value="{{ old('last_name', $user->last_name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       required>
                @error('last_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="md:col-span-2">
                <label for="phone_e164" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('Numer telefonu') }}
                </label>
                <input type="tel" name="phone_e164" id="phone_e164"
                       value="{{ old('phone_e164', $user->phone_e164) }}"
                       placeholder="+48123456789"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="mt-1 text-sm text-gray-500">{{ __('Format międzynarodowy, np. +48123456789') }}</p>
                @error('phone_e164')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Address Section --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-base font-semibold text-gray-800 mb-4">{{ __('Adres zamieszkania') }}</h3>
            <p class="text-sm text-gray-600 mb-4">
                {{ __('Twój adres domowy do celów fakturowania i kontaktu. Nie musi być taki sam jak miejsce wykonania usługi.') }}
            </p>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Street Name --}}
                <div class="md:col-span-2">
                    <label for="street_name" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Ulica') }}
                    </label>
                    <input type="text" name="street_name" id="street_name"
                           value="{{ old('street_name', $user->street_name) }}"
                           placeholder="{{ __('np. Marszałkowska') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('street_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Street Number --}}
                <div class="md:col-span-2">
                    <label for="street_number" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Numer budynku / lokalu') }}
                    </label>
                    <input type="text" name="street_number" id="street_number"
                           value="{{ old('street_number', $user->street_number) }}"
                           placeholder="{{ __('np. 12/34') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('street_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- City --}}
                <div class="md:col-span-2">
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Miasto') }}
                    </label>
                    <input type="text" name="city" id="city"
                           value="{{ old('city', $user->city) }}"
                           placeholder="{{ __('np. Warszawa') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Postal Code --}}
                <div class="md:col-span-2">
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Kod pocztowy') }}
                    </label>
                    <input type="text" name="postal_code" id="postal_code"
                           value="{{ old('postal_code', $user->postal_code) }}"
                           placeholder="00-000"
                           maxlength="6"
                           x-data="{ formatPostalCode(e) {
                               let val = e.target.value.replace(/[^0-9]/g, '');
                               if (val.length >= 2) {
                                   e.target.value = val.substring(0, 2) + '-' + val.substring(2, 5);
                               } else {
                                   e.target.value = val;
                               }
                           } }"
                           @input="formatPostalCode($event)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="mt-1 text-sm text-gray-500">{{ __('Format: XX-XXX') }}</p>
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Email (read-only, change via security tab) --}}
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Adres email') }}
            </label>
            <div class="flex items-center justify-between">
                <span class="text-gray-800">{{ $user->email }}</span>
                <button type="button" onclick="window.location.hash='security'"
                        class="text-sm text-primary-600 hover:text-primary-800">
                    {{ __('Zmień email') }} &rarr;
                </button>
            </div>
            @if($user->hasPendingEmailChange())
                <p class="mt-2 text-sm text-yellow-600">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('Oczekuje na potwierdzenie zmiany na:') }} {{ $user->pending_email }}
                </p>
            @endif
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                {{ __('Zapisz zmiany') }}
            </button>
        </div>
    </form>
</div>
