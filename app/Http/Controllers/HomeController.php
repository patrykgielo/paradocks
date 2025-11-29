<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\Settings\SettingsManager;

class HomeController extends Controller
{
    public function __construct(private readonly SettingsManager $settings) {}

    public function index()
    {
        $services = Service::active()
            ->ordered()
            ->get();

        return view('home', [
            'services' => $services,
            'marketingContent' => $this->settings->marketingContent(),
        ]);
    }
}
