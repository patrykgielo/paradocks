<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wkrótce startujemy - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</head>

@php
// Background logic: custom image OR gradient fallback
$bgStyle = !empty($config['image_url'])
    ? "background-image: url('{$config['image_url']}')"
    : "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)";

// Contact information
$contact = app(\App\Support\Settings\SettingsManager::class)->contactInformation();
@endphp

<body>
    <!-- Full-Screen Background Container -->
    <div class="relative min-h-screen bg-cover bg-center bg-fixed" style="{{ $bgStyle }}">

        <!-- 75% Black Overlay -->
        <div class="absolute inset-0 bg-black/75"></div>

        <!-- Content (z-index above overlay) -->
        <div class="relative z-10 min-h-screen flex items-center justify-center p-4 sm:p-6">

            <!-- Glassmorphism Content Card -->
            <div class="w-full max-w-xl md:max-w-2xl bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 p-6 sm:p-8 md:p-10">

                <!-- Logo/Icon -->
                <div class="flex justify-center mb-6">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 animate-float">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>

                <!-- Main Heading -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white text-center mb-4">
                    Wkrótce Startujemy!
                </h1>

                <!-- Subheading -->
                <p class="text-base sm:text-lg md:text-xl text-white/90 text-center mb-8 leading-relaxed">
                    Przygotowujemy coś wyjątkowego dla Ciebie
                </p>

                <!-- Custom Message or Default -->
                @if(isset($config['message']) && $config['message'])
                    <div class="text-center mb-8">
                        <p class="text-base sm:text-lg text-white/80 leading-relaxed">
                            {{ $config['message'] }}
                        </p>
                    </div>
                @else
                    <div class="text-center mb-8">
                        <p class="text-base text-white/80 leading-relaxed">
                            Pracujemy nad uruchomieniem najlepszego systemu rezerwacji detailingu samochodowego.
                            Niedługo będziesz mógł zarezerwować profesjonalną pielęgnację swojego pojazdu w zaledwie kilka kliknięć.
                        </p>
                    </div>
                @endif

                <!-- Launch Date (if provided) -->
                @if(isset($config['launch_date']) && $config['launch_date'])
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 mb-8 text-center">
                        <p class="text-sm text-white/80 uppercase tracking-wider mb-2 font-semibold">
                            Data startu
                        </p>
                        <p class="text-2xl md:text-3xl font-bold text-white">
                            {{ $config['launch_date'] }}
                        </p>
                    </div>
                @endif

                <!-- Contact Info -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                    <h3 class="text-lg font-semibold text-white mb-4">Masz pytania?</h3>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @if(!empty($contact['email']))
                            <a href="mailto:{{ $contact['email'] }}"
                               class="flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $contact['email'] }}
                            </a>
                        @endif

                        @if(!empty($contact['phone']))
                            <a href="tel:{{ $contact['phone'] }}"
                               class="flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $contact['phone'] }}
                            </a>
                        @endif
                    </div>

                    @if(!empty($contact['address']))
                        <div class="mt-4 flex items-center justify-center text-sm text-white/70">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $contact['address'] }}</span>
                        </div>
                    @endif
                </div>

            </div>

        </div>

        <!-- Footer -->
        <div class="absolute bottom-0 left-0 right-0 z-10 p-6 text-center">
            <p class="text-sm text-white/60">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Wszelkie prawa zastrzeżone.
            </p>
        </div>

    </div>
</body>
</html>
