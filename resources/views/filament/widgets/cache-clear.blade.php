<x-filament-widgets::widget>
    <x-slot name="heading">
        Cache Management
    </x-slot>

    <x-slot name="description">
        Quick cache operations for development and troubleshooting
    </x-slot>

    {{-- Cache Operations Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 dark:border-gray-700">
                <tr class="text-left">
                    <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Cache Layer</th>
                    <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Contains</th>
                    <th class="py-2 text-right font-medium text-gray-700 dark:text-gray-300">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                {{-- Application Cache --}}
                <tr>
                    <td class="py-3 pr-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Application</span>
                        </div>
                    </td>
                    <td class="py-3 pr-4 text-xs text-gray-600 dark:text-gray-400">
                        Settings, service areas, bookings
                    </td>
                    <td class="py-3 text-right">
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="clearApplicationCache"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="clearApplicationCache">Clear</span>
                            <span wire:loading wire:target="clearApplicationCache">...</span>
                        </x-filament::button>
                    </td>
                </tr>

                {{-- Config Cache --}}
                <tr>
                    <td class="py-3 pr-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Config</span>
                        </div>
                    </td>
                    <td class="py-3 pr-4 text-xs text-gray-600 dark:text-gray-400">
                        Configuration, routes, views
                    </td>
                    <td class="py-3 text-right">
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="clearConfigCache"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="clearConfigCache">Clear</span>
                            <span wire:loading wire:target="clearConfigCache">...</span>
                        </x-filament::button>
                    </td>
                </tr>

                {{-- Filament Cache --}}
                <tr>
                    <td class="py-3 pr-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100">Filament</span>
                        </div>
                    </td>
                    <td class="py-3 pr-4 text-xs text-gray-600 dark:text-gray-400">
                        Components, icons
                    </td>
                    <td class="py-3 text-right">
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="clearFilamentCache"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="clearFilamentCache">Clear</span>
                            <span wire:loading wire:target="clearFilamentCache">...</span>
                        </x-filament::button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Compact Info Banner --}}
    <div class="mt-3 flex items-start gap-2 p-2 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded text-xs">
        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <span class="text-gray-600 dark:text-gray-400">
            <strong class="font-medium">Note:</strong> OPcache requires container restart. Use: <code class="px-1 py-0.5 bg-gray-200 dark:bg-gray-800 rounded font-mono">docker compose restart app</code>
        </span>
    </div>
</x-filament-widgets::widget>
