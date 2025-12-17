<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\HomePage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomePageObserver
{
    /**
     * Handle the HomePage "updated" event.
     */
    public function updated(HomePage $homePage): void
    {
        $this->clearCache();
    }

    /**
     * Handle the HomePage "saved" event.
     */
    public function saved(HomePage $homePage): void
    {
        $this->clearCache();
    }

    /**
     * Clear all home page caches.
     */
    private function clearCache(): void
    {
        Cache::forget('home.full_page');
        Cache::forget('home.sections');

        Log::info('Home page cache cleared');
    }
}
