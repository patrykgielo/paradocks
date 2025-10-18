<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // Note: Staff selection has been removed from the booking flow
        // Staff is automatically assigned based on availability
        $user = Auth::user();

        return view('booking.create', [
            'service' => $service,
            'user' => $user,
        ]);
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
        ]);

        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        // Check if date meets advance booking requirement
        $minDateTime = now()->addHours(config('booking.advance_booking_hours', 24));
        $requestedDate = Carbon::parse($request->date . ' 00:00:00');

        if ($requestedDate->lt($minDateTime->startOfDay())) {
            return response()->json([
                'slots' => [],
                'date' => $date->format('Y-m-d'),
                'message' => 'Rezerwacja musi być dokonana co najmniej 24 godziny przed terminem wizyty.',
            ]);
        }

        // Get available slots across ALL staff members
        $slots = $this->appointmentService->getAvailableSlotsAcrossAllStaff(
            serviceId: $request->service_id,
            date: $date,
            serviceDurationMinutes: $service->duration_minutes
        );

        return response()->json([
            'slots' => $slots,
            'date' => $date->format('Y-m-d'),
        ]);
    }
}
