<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - System Rezerwacji</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    @php($contact = app(\App\Support\Settings\SettingsManager::class)->contactInformation())
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">
                    {{ config('app.name') }}
                </a>

                <div class="hidden md:flex space-x-6 items-center">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600">Strona główna</a>

                    @auth
                        <a href="{{ route('appointments.index') }}" class="text-gray-700 hover:text-blue-600">Moje wizyty</a>

                        @if(Auth::user()->isAdmin() || Auth::user()->isStaff())
                            <a href="/admin" class="text-gray-700 hover:text-blue-600">Panel Admina</a>
                        @endif

                        <div class="relative group">
                            <button class="flex items-center text-gray-700 hover:text-blue-600">
                                {{ Auth::user()->full_name }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden group-hover:block z-50">
                                <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Moje konto
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Wyloguj
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Zaloguj się</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Zarejestruj się
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content (Full Width - sections control their own containers) -->
    <main class="flex-1">
        {{-- Flash Messages Container --}}
        <div class="container mx-auto px-4 py-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <!-- Footer (iOS Component) -->
    <x-ios.footer />

    @stack('scripts')
</body>
</html>
