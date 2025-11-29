@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Sidebar Navigation --}}
        <aside class="lg:w-64 flex-shrink-0">
            {{-- Mobile: Horizontal scroll tabs --}}
            <nav class="lg:hidden bg-white rounded-lg shadow-md p-2 mb-4">
                <div class="flex space-x-2 overflow-x-auto">
                    @foreach([
                        ['route' => 'profile.personal', 'icon' => 'user', 'label' => 'Dane osobowe'],
                        ['route' => 'profile.vehicle', 'icon' => 'car', 'label' => 'Pojazd'],
                        ['route' => 'profile.address', 'icon' => 'map-pin', 'label' => 'Adres'],
                        ['route' => 'profile.notifications', 'icon' => 'bell', 'label' => 'Powiadomienia'],
                        ['route' => 'profile.security', 'icon' => 'shield', 'label' => 'Bezpieczeństwo'],
                    ] as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center whitespace-nowrap px-3 py-2 rounded-lg text-sm transition-colors
                                  {{ request()->routeIs($item['route'])
                                     ? 'bg-blue-600 text-white'
                                     : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            @include('profile.partials.icons.' . $item['icon'], ['class' => 'w-4 h-4 mr-1.5'])
                            <span>{{ __($item['label']) }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>

            {{-- Desktop: Vertical sidebar --}}
            <nav class="hidden lg:block bg-white rounded-lg shadow-md p-4 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Moje konto') }}</h2>
                <ul class="space-y-1">
                    @foreach([
                        ['route' => 'profile.personal', 'icon' => 'user', 'label' => 'Dane osobowe'],
                        ['route' => 'profile.vehicle', 'icon' => 'car', 'label' => 'Mój pojazd'],
                        ['route' => 'profile.address', 'icon' => 'map-pin', 'label' => 'Mój adres'],
                        ['route' => 'profile.notifications', 'icon' => 'bell', 'label' => 'Powiadomienia'],
                        ['route' => 'profile.security', 'icon' => 'shield', 'label' => 'Bezpieczeństwo'],
                    ] as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center px-3 py-2 rounded-lg transition-colors
                                      {{ request()->routeIs($item['route'])
                                         ? 'bg-blue-50 text-blue-700 font-medium'
                                         : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                @include('profile.partials.icons.' . $item['icon'], ['class' => 'w-5 h-5'])
                                <span class="ml-3">{{ __($item['label']) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 min-w-0">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @yield('profile-content')
        </main>
    </div>
</div>
@endsection
