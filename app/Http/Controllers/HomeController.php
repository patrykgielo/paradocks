<?php

namespace App\Http\Controllers;

use App\Models\HomePage;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Display home page.
     *
     * Uses aggressive caching (1-hour TTL) for performance.
     * Cache automatically cleared by HomePageObserver on updates.
     */
    public function index()
    {
        $page = Cache::remember('home.full_page', 3600, function () {
            return HomePage::getInstance();
        });

        return view('home-dynamic', compact('page'));
    }
}
