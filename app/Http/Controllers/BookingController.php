<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->middleware('auth');
        $this->appointmentService = $appointmentService;
    }

    public function create(Service $service)
    {
        // Get staff members who have availability for this service
        $staffMembers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['staff', 'admin', 'super-admin']);
        })->whereHas('serviceAvailabilities', function ($query) use ($service) {
            $query->where('service_id', $service->id);
        })->get();

        return view('booking.create', compact('service', 'staffMembers'));
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $slots = $this->appointmentService->getAvailableTimeSlots(
            serviceId: $request->service_id,
            staffId: $request->staff_id,
            date: $date,
            serviceDurationMinutes: $service->duration_minutes
        );

        return response()->json([
            'slots' => $slots,
            'date' => $date->format('Y-m-d'),
        ]);
    }
}
