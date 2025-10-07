@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">Moje wizyty</h1>

    @if($appointments->isEmpty())
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
            <p class="text-lg mb-4">Nie masz jeszcze żadnych wizyt.</p>
            <a href="{{ route('home') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 inline-block">
                Przeglądaj dostępne usługi
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($appointments as $appointment)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-xl font-bold mr-3">{{ $appointment->service->name }}</h3>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        @if($appointment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($appointment->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800
                                        @elseif($appointment->status === 'completed') bg-gray-100 text-gray-800
                                        @endif">
                                        @if($appointment->status === 'pending') Oczekująca
                                        @elseif($appointment->status === 'confirmed') Potwierdzona
                                        @elseif($appointment->status === 'cancelled') Anulowana
                                        @elseif($appointment->status === 'completed') Zakończona
                                        @endif
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ $appointment->appointment_date->format('d.m.Y') }}</span>
                                    </div>

                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</span>
                                    </div>

                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>{{ $appointment->staff->name }}</span>
                                    </div>
                                </div>

                                @if($appointment->notes)
                                    <div class="mt-4 text-sm text-gray-600">
                                        <strong>Uwagi:</strong> {{ $appointment->notes }}
                                    </div>
                                @endif

                                @if($appointment->cancellation_reason)
                                    <div class="mt-4 text-sm text-red-600">
                                        <strong>Powód anulowania:</strong> {{ $appointment->cancellation_reason }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 md:mt-0 md:ml-6">
                                @if($appointment->can_be_cancelled)
                                    <form method="POST" action="{{ route('appointments.cancel', $appointment) }}"
                                          onsubmit="return confirm('Czy na pewno chcesz anulować tę wizytę?');">
                                        @csrf
                                        <button type="submit"
                                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                                            Anuluj wizytę
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
