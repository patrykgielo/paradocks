<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of all published services.
     */
    public function index()
    {
        $services = Service::published()
            ->active()
            ->ordered()
            ->get();

        return view('services.index', compact('services'));
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        // Only show published services (404 for drafts/scheduled)
        abort_unless($service->isPublished(), 404);

        // Get related services (same area, similar price, or random)
        $relatedServices = Service::published()
            ->active()
            ->where('id', '!=', $service->id)
            ->ordered()
            ->limit(3)
            ->get();

        return view('services.show', [
            'service' => $service,
            'relatedServices' => $relatedServices,
        ]);
    }
}
