<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filters Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Date Range --}}
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Od daty
                    </label>
                    <input
                        type="date"
                        id="start_date"
                        wire:model.live="startDate"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Do daty
                    </label>
                    <input
                        type="date"
                        id="end_date"
                        wire:model.live="endDate"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>

                <div>
                    <label for="staff_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pracownik (opcjonalnie)
                    </label>
                    <select
                        id="staff_filter"
                        wire:model.live="selectedStaff"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">Wszyscy pracownicy</option>
                        @foreach(\App\Models\User::role('staff')->orderBy('first_name')->get() as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>Legenda:</strong>
                    <span class="ml-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-400 mr-2">
                            Harmonogram Bazowy
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400 mr-2">
                            Wyjątek
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400">
                            Urlop
                        </span>
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Pokazuje dane z {{ $startDate }} do {{ $endDate }}
                </div>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-blue-700 dark:text-blue-400">
                        <strong>Nowy widok skonsolidowany.</strong>
                        Ten widok łączy harmonogramy bazowe, wyjątki i urlopy w jednym miejscu.
                        Wcześniejsze osobne widoki są nadal dostępne przez
                        <a href="/admin/employees" class="underline">edycję pracownika</a> → zakładki.
                    </p>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
