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

        // Check if date meets 24-hour advance booking requirement
        // We check the EARLIEST possible slot (business hours start) to be conservative
        $businessHours = config('booking.business_hours');
        $earliestSlotDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHours['start']);

        if (!$this->appointmentService->meetsAdvanceBookingRequirement($earliestSlotDateTime)) {
            $minDateTime = now()->addHours(config('booking.advance_booking_hours', 24));

            return response()->json([
                'slots' => [],
                'date' => $date->format('Y-m-d'),
                'message' => 'Rezerwacje możliwe dopiero od ' . $minDateTime->format('d.m.Y H:i'),
                'reason' => 'advance_booking_not_met'
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
