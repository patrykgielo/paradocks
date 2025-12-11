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
