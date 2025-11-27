<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use App\Services\AppointmentService;
use App\Support\Settings\SettingsManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected AppointmentService $appointmentService;
    protected SettingsManager $settings;

    public function __construct(AppointmentService $appointmentService, SettingsManager $settings)
    {
        $this->middleware('auth');
        $this->appointmentService = $appointmentService;
        $this->settings = $settings;
    }

    public function create(Service $service)
    {
        // Note: Staff selection has been removed from the booking flow
        // Staff is automatically assigned based on availability
        $user = Auth::user();

        // Load user's saved default vehicle and address for prefilling
        $savedVehicle = null;
        $savedAddress = null;

        if ($user) {
            // Get default vehicle or first vehicle if no default
            $savedVehicle = $user->vehicle ?? $user->vehicles()->first();

            // Get default address or first address if no default
            $savedAddress = $user->address ?? $user->addresses()->first();
        }

        return view('booking.create', [
            'service' => $service,
            'user' => $user,
            'savedVehicle' => $savedVehicle,
            'savedAddress' => $savedAddress,
            'bookingConfig' => $this->settings->bookingConfiguration(),
            'mapConfig' => $this->settings->mapConfiguration(),
            'marketingContent' => $this->settings->marketingContent(),
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
        $businessHours = $this->settings->bookingBusinessHours();
        $earliestSlotDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHours['start']);

        if (!$this->appointmentService->meetsAdvanceBookingRequirement($earliestSlotDateTime)) {
            $minDateTime = now()->addHours($this->settings->advanceBookingHours());

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
