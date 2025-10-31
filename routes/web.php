<?php

use App\Http\Controllers\Api\VehicleDataController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Auth::routes();

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Booking
    Route::get('/services/{service}/book', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/api/available-slots', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');

    // Vehicle Data API
    Route::get('/api/vehicle-types', [VehicleDataController::class, 'vehicleTypes'])->name('api.vehicle-types');
    Route::get('/api/car-brands', [VehicleDataController::class, 'brands'])->name('api.car-brands');
    Route::get('/api/car-models', [VehicleDataController::class, 'models'])->name('api.car-models');
    Route::get('/api/vehicle-years', [VehicleDataController::class, 'years'])->name('api.vehicle-years');

    // Appointments
    Route::get('/my-appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
});
