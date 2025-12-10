<footer class="bg-gray-50 border-t border-gray-200 mt-16">
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            {{-- Column 1: Logo & Company Info --}}
            <div class="col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white font-bold text-xl">P</span>
                    </div>
                    <span class="text-xl font-semibold text-gray-900">Paradocks</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Profesjonalne usługi detailingowe dla Twojego auta
                </p>
                <div class="flex gap-3">
                    <a href="tel:+48123456789"
                       class="w-9 h-9 rounded-full bg-gray-200 hover:bg-primary hover:text-white flex items-center justify-center transition-all duration-200 ios-spring"
                       aria-label="Zadzwoń do nas">
                        <x-heroicon-s-phone class="w-4 h-4" />
                    </a>
                    <a href="mailto:kontakt@paradocks.pl"
                       class="w-9 h-9 rounded-full bg-gray-200 hover:bg-primary hover:text-white flex items-center justify-center transition-all duration-200 ios-spring"
                       aria-label="Napisz do nas">
                        <x-heroicon-s-envelope class="w-4 h-4" />
                    </a>
                </div>
            </div>

            {{-- Column 2: Quick Links --}}
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Szybkie linki</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('home') }}"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Strona główna
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('services.index') }}"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Usługi
                        </a>
                    </li>
                    @auth
                    <li>
                        <a href="{{ route('booking.step', 1) }}"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Zarezerwuj termin
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.personal') }}"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Moje konto
                        </a>
                    </li>
                    @else
                    <li>
                        <a href="{{ route('login') }}"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Logowanie
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('register') }}"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Rejestracja
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>

            {{-- Column 3: Company --}}
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Firma</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            O nas
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Kontakt
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Cennik
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Portfolio
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 4: Legal --}}
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Informacje prawne</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Polityka prywatności
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Regulamin
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            Polityka cookies
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="text-sm text-gray-600 hover:text-primary transition-colors duration-200">
                            RODO
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="pt-8 border-t border-gray-200">
            <p class="text-center text-sm text-gray-600">
                &copy; {{ date('Y') }} Paradocks. Wszelkie prawa zastrzeżone.
            </p>
        </div>
    </div>
</footer>
