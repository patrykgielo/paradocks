<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->middleware('auth');
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $appointments = Auth::user()
            ->customerAppointments()
            ->with(['service', 'staff'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return view('appointments.index', compact('appointments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate availability
        $validation = $this->appointmentService->validateAppointment(
            staffId: $validated['staff_id'],
            serviceId: $validated['service_id'],
            appointmentDate: $validated['appointment_date'],
            startTime: $validated['start_time'],
            endTime: $validated['end_time']
        );

        if (!$validation['valid']) {
            return back()
                ->withErrors(['appointment' => $validation['errors']])
                ->withInput();
        }

        // Create appointment
        $appointment = Appointment::create([
            'service_id' => $validated['service_id'],
            'customer_id' => Auth::id(),
            'staff_id' => $validated['staff_id'],
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Wizyta została pomyślnie zarezerwowana! Status: Oczekująca na potwierdzenie.');
    }

    public function cancel(Appointment $appointment)
    {
        // Check if user owns this appointment
        if ($appointment->customer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if appointment can be cancelled
        if (!$appointment->can_be_cancelled) {
            return back()->withErrors(['appointment' => 'Ta wizyta nie może być anulowana.']);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'Anulowane przez klienta',
        ]);

        return back()->with('success', 'Wizyta została anulowana.');
    }
}
