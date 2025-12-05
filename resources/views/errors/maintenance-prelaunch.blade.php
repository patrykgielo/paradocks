<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wkrótce startujemy - Paradocks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 1s ease-out;
        }
    </style>
</head>
<body>
    <!-- Full-Screen Background -->
    <div class="relative min-h-screen bg-cover bg-center bg-fixed"
         style="background-image: url('/images/maintenance-background.png'); background-color: #0f1419;">

        <!-- Dark Overlay (80% for better contrast) -->
        <div class="absolute inset-0 bg-black/80"></div>

        <!-- Content -->
        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">

            <!-- Logo (ABOVE card) -->
            <div class="flex justify-center mb-8 sm:mb-12 animate-fadeIn">
                <img src="/images/logo.svg"
                     alt="Paradocks - Mobilne Myjnie Parowe"
                     class="w-40 sm:w-56 md:w-72 h-auto drop-shadow-2xl">
            </div>

            <!-- Main Content Card -->
            <div class="w-full max-w-xl md:max-w-2xl bg-gray-900/60 backdrop-blur-lg rounded-2xl shadow-2xl border border-cyan-500/30 p-6 sm:p-8 md:p-10 animate-fadeIn">

                <!-- Main Heading -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white text-center mb-4">
                    Wkrótce Startujemy!
                </h1>

                <!-- Subheading -->
                <p class="text-base sm:text-lg md:text-xl text-gray-200 text-center mb-8 leading-relaxed">
                    Przygotowujemy coś wyjątkowego dla Ciebie
                </p>

                <!-- Launch Date -->
                <div class="text-center mb-8 pb-8 border-b border-cyan-500/20">
                    <p class="text-sm text-cyan-400 mb-2 tracking-wide uppercase">Data startu</p>
                    <p class="text-5xl sm:text-6xl md:text-7xl font-bold text-white">
                        03.01.2026
                    </p>
                </div>

                <!-- Main Message -->
                <div class="text-center mb-8">
                    <p class="text-base text-gray-300 leading-relaxed">
                        Pracujemy nad uruchomieniem najlepszego systemu rezerwacji detailingu samochodowego.
                        Niedługo będziesz mógł zarezerwować profesjonalną pielęgnację swojego pojazdu w zaledwie kilka kliknięć.
                    </p>
                </div>

                <!-- Contact Info -->
                <div class="bg-gray-800/40 backdrop-blur-sm border border-cyan-500/20 rounded-xl p-6 text-center">
                    <h3 class="text-lg font-semibold text-white mb-4">Masz pytania?</h3>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="mailto:kontakt@paradocks.pl"
                           class="flex items-center px-5 py-3 bg-cyan-600/20 hover:bg-cyan-500/30 border border-cyan-400/30 rounded-lg text-white transition-all duration-200 shadow-lg hover:shadow-cyan-500/20">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            kontakt@paradocks.pl
                        </a>

                        <a href="tel:+48123456789"
                           class="flex items-center px-5 py-3 bg-cyan-600/20 hover:bg-cyan-500/30 border border-cyan-400/30 rounded-lg text-white transition-all duration-200 shadow-lg hover:shadow-cyan-500/20">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            +48 123 456 789
                        </a>
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer -->
        <div class="absolute bottom-0 left-0 right-0 z-10 p-6 text-center">
            <p class="text-sm text-gray-400">
                &copy; <script>document.write(new Date().getFullYear())</script> Paradocks. Wszelkie prawa zastrzeżone.
            </p>
        </div>

    </div>
</body>
</html>
