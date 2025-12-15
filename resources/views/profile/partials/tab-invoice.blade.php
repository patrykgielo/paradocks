<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Dane do faktury') }}</h2>

    @if($invoiceProfile)
        {{-- Display existing invoice profile --}}
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-blue-900">Profil faktury zapisany</h3>
                    <p class="mt-1 text-sm text-blue-700">
                        Zapisano: {{ $invoiceProfile->created_at->format('d.m.Y H:i') }}<br>
                        Ostatnia aktualizacja: {{ $invoiceProfile->updated_at->format('d.m.Y H:i') }}
                    </p>
                    @if($invoiceProfile->consent_given_at)
                        <p class="mt-2 text-xs text-blue-600">
                            Zgoda udzielona: {{ $invoiceProfile->consent_given_at->format('d.m.Y H:i') }}
                            @if($invoiceProfile->consent_ip)
                                <br>IP: {{ $invoiceProfile->consent_ip }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Edit Form --}}
        <form action="{{ route('profile.invoice.update') }}" method="POST" x-data="invoiceForm()">
            @csrf
            @method('PATCH')

            <div class="space-y-4">
                {{-- Invoice Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Typ faktury') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="type" value="individual"
                                   {{ old('type', $invoiceProfile->type) == 'individual' ? 'checked' : '' }}
                                   x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Na osobę prywatną') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="company"
                                   {{ old('type', $invoiceProfile->type) == 'company' ? 'checked' : '' }}
                                   x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Faktura firmowa (z NIP)') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="foreign_eu"
                                   {{ old('type', $invoiceProfile->type) == 'foreign_eu' ? 'checked' : '' }}
                                   x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Firma zagraniczna (UE z VAT ID)') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="foreign_non_eu"
                                   {{ old('type', $invoiceProfile->type) == 'foreign_non_eu' ? 'checked' : '' }}
                                   x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Firma spoza UE') }}</span>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Company Name --}}
                <div x-show="['company', 'foreign_eu', 'foreign_non_eu'].includes(invoiceType)">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Nazwa firmy') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company_name" id="company_name"
                           value="{{ old('company_name', $invoiceProfile->company_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="np. Paradocks Sp. z o.o.">
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- NIP --}}
                    <div x-show="invoiceType === 'company'">
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('NIP') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nip" id="nip"
                               value="{{ old('nip', $invoiceProfile->nip) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="123-456-78-90"
                               maxlength="13">
                        <p class="mt-1 text-xs text-gray-500">{{ __('Format: XXX-XXX-XX-XX') }}</p>
                        @error('nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- VAT ID --}}
                    <div x-show="invoiceType === 'foreign_eu'">
                        <label for="vat_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Numer VAT UE') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="vat_id" id="vat_id"
                               value="{{ old('vat_id', $invoiceProfile->vat_id) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. DE123456789">
                        @error('vat_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- REGON --}}
                    <div x-show="invoiceType === 'company'">
                        <label for="regon" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('REGON') }} <span class="text-gray-400">({{ __('opcjonalny') }})</span>
                        </label>
                        <input type="text" name="regon" id="regon"
                               value="{{ old('regon', $invoiceProfile->regon) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="9 lub 14 cyfr"
                               maxlength="14">
                        @error('regon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Address Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="street" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Ulica') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="street" id="street"
                               value="{{ old('street', $invoiceProfile->street) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. Poznańska"
                               required>
                        @error('street')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="street_number" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Numer') }}
                        </label>
                        <input type="text" name="street_number" id="street_number"
                               value="{{ old('street_number', $invoiceProfile->street_number) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. 42A">
                        @error('street_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Kod pocztowy') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="postal_code" id="postal_code"
                               value="{{ old('postal_code', $invoiceProfile->postal_code) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="60-123"
                               maxlength="6"
                               required>
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Miasto') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" id="city"
                               value="{{ old('city', $invoiceProfile->city) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. Poznań"
                               required>
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Kraj') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="country" id="country"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                required>
                            <option value="PL" {{ old('country', $invoiceProfile->country) == 'PL' ? 'selected' : '' }}>{{ __('Polska') }}</option>
                            <option value="DE" {{ old('country', $invoiceProfile->country) == 'DE' ? 'selected' : '' }}>{{ __('Niemcy') }}</option>
                            <option value="CZ" {{ old('country', $invoiceProfile->country) == 'CZ' ? 'selected' : '' }}>{{ __('Czechy') }}</option>
                            <option value="SK" {{ old('country', $invoiceProfile->country) == 'SK' ? 'selected' : '' }}>{{ __('Słowacja') }}</option>
                            <option value="UA" {{ old('country', $invoiceProfile->country) == 'UA' ? 'selected' : '' }}>{{ __('Ukraina') }}</option>
                            <option value="GB" {{ old('country', $invoiceProfile->country) == 'GB' ? 'selected' : '' }}>{{ __('Wielka Brytania') }}</option>
                            <option value="US" {{ old('country', $invoiceProfile->country) == 'US' ? 'selected' : '' }}>{{ __('USA') }}</option>
                        </select>
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <button type="button" onclick="confirmDeleteInvoiceProfile()"
                        class="px-4 py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                    {{ __('Usuń profil faktury') }}
                </button>

                <button type="submit"
                        class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    {{ __('Zapisz zmiany') }}
                </button>
            </div>
        </form>

        {{-- Delete Form (hidden) --}}
        <form id="delete-invoice-profile-form" action="{{ route('profile.invoice.destroy') }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

    @else
        {{-- No invoice profile - show create form --}}
        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900">{{ __('Brak zapisanych danych do faktury') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Zapisz swoje dane do faktury, aby przyspieszyć proces rezerwacji w przyszłości. Dane będą automatycznie wpisywane podczas każdego zamówienia.') }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('profile.invoice.store') }}" method="POST" x-data="invoiceForm()">
            @csrf

            <div class="space-y-4">
                {{-- Same form fields as above but without old values --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Typ faktury') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="type" value="individual" checked x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Na osobę prywatną') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="company" x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Faktura firmowa (z NIP)') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="foreign_eu" x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Firma zagraniczna (UE z VAT ID)') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="foreign_non_eu" x-model="invoiceType"
                                   class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                            <span class="ml-2 text-gray-900">{{ __('Firma spoza UE') }}</span>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Repeat all fields from above but without old values from $invoiceProfile --}}
                <div x-show="['company', 'foreign_eu', 'foreign_non_eu'].includes(invoiceType)">
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Nazwa firmy') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="np. Paradocks Sp. z o.o.">
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-show="invoiceType === 'company'">
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('NIP') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="123-456-78-90" maxlength="13">
                        <p class="mt-1 text-xs text-gray-500">{{ __('Format: XXX-XXX-XX-XX') }}</p>
                        @error('nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="invoiceType === 'foreign_eu'">
                        <label for="vat_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Numer VAT UE') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="vat_id" id="vat_id" value="{{ old('vat_id') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. DE123456789">
                        @error('vat_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="invoiceType === 'company'">
                        <label for="regon" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('REGON') }} <span class="text-gray-400">({{ __('opcjonalny') }})</span>
                        </label>
                        <input type="text" name="regon" id="regon" value="{{ old('regon') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="9 lub 14 cyfr" maxlength="14">
                        @error('regon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="street" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Ulica') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="street" id="street" value="{{ old('street') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. Poznańska" required>
                        @error('street')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="street_number" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Numer') }}
                        </label>
                        <input type="text" name="street_number" id="street_number" value="{{ old('street_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. 42A">
                        @error('street_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Kod pocztowy') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="60-123" maxlength="6" required>
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Miasto') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="np. Poznań" required>
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Kraj') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="country" id="country"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                required>
                            <option value="PL" selected>{{ __('Polska') }}</option>
                            <option value="DE">{{ __('Niemcy') }}</option>
                            <option value="CZ">{{ __('Czechy') }}</option>
                            <option value="SK">{{ __('Słowacja') }}</option>
                            <option value="UA">{{ __('Ukraina') }}</option>
                            <option value="GB">{{ __('Wielka Brytania') }}</option>
                            <option value="US">{{ __('USA') }}</option>
                        </select>
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- GDPR Consent Info --}}
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-900">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Zapisując dane do faktury wyrażasz zgodę na ich przechowywanie zgodnie z') }}
                        <a href="/polityka-prywatnosci" target="_blank" class="text-blue-700 underline hover:text-blue-900">
                            {{ __('Polityką Prywatności') }}
                        </a>.
                        {{ __('Dane będą przechowywane minimum 5 lat zgodnie z ustawą o rachunkowości.') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    {{ __('Zapisz dane do faktury') }}
                </button>
            </div>
        </form>
    @endif

    {{-- Data Retention Info --}}
    <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
        <h3 class="text-sm font-medium text-gray-900 mb-2">{{ __('Informacje o przetwarzaniu danych') }}</h3>
        <ul class="text-sm text-gray-600 space-y-1">
            <li class="flex items-start">
                <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('Dane przechowywane minimum 5 lat (ustawa o rachunkowości)') }}</span>
            </li>
            <li class="flex items-start">
                <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('Możesz edytować lub usunąć dane w dowolnym momencie') }}</span>
            </li>
            <li class="flex items-start">
                <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('Dane automatycznie wpisywane podczas rezerwacji') }}</span>
            </li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
function invoiceForm() {
    return {
        invoiceType: '{{ old('type', $invoiceProfile->type ?? 'individual') }}',
    }
}

function confirmDeleteInvoiceProfile() {
    if (confirm('{{ __('Czy na pewno chcesz usunąć zapisany profil faktury? Dane z dotychczasowych rezerwacji nie zostaną usunięte.') }}')) {
        document.getElementById('delete-invoice-profile-form').submit();
    }
}
</script>
@endpush
