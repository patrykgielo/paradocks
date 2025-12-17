<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Cache Management
        </x-slot>

        <x-slot name="description">
            Quick cache clearing for application, configuration, and Filament components.
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Application Cache --}}
            <div class="flex flex-col space-y-2">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Application Cache
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    Clears settings, service areas, and booking availability cache.
                </div>
                <x-filament::button
                    color="warning"
                    wire:click="clearApplicationCache"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="clearApplicationCache">
                        Clear Application Cache
                    </span>
                    <span wire:loading wire:target="clearApplicationCache">
                        Clearing...
                    </span>
                </x-filament::button>
            </div>

            {{-- Config Cache --}}
            <div class="flex flex-col space-y-2">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Config Cache
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    Clears configuration, routes, and views cache.
                </div>
                <x-filament::button
                    color="warning"
                    wire:click="clearConfigCache"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="clearConfigCache">
                        Clear Config Cache
                    </span>
                    <span wire:loading wire:target="clearConfigCache">
                        Clearing...
                    </span>
                </x-filament::button>
            </div>

            {{-- Filament Cache --}}
            <div class="flex flex-col space-y-2">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Filament Cache
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    Clears Filament components and icons cache.
                </div>
                <x-filament::button
                    color="warning"
                    wire:click="clearFilamentCache"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="clearFilamentCache">
                        Clear Filament Cache
                    </span>
                    <span wire:loading wire:target="clearFilamentCache">
                        Clearing...
                    </span>
                </x-filament::button>
            </div>
        </div>

        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Note:</strong> PHP OPcache requires container restart in production. For code changes to take effect, restart the app container: <code class="px-1 py-0.5 bg-blue-100 dark:bg-blue-800 rounded">docker compose restart app</code>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
