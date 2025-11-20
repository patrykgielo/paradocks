<?php

use App\Http\Controllers\Api\SmsApiIncomingController;
use App\Http\Controllers\Api\SmsApiWebhookController;
use App\Http\Controllers\Api\VehicleDataController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Locale switching (toggle between pl/en)
Route::get('/locale/toggle', function () {
    $currentLocale = app()->getLocale();
    $availableLocales = config('app.available_locales', ['pl', 'en']);

    // Find next locale in rotation (pl → en → pl)
    $currentIndex = array_search($currentLocale, $availableLocales);
    $nextIndex = ($currentIndex + 1) % count($availableLocales);
    $newLocale = $availableLocales[$nextIndex];

    // Update user preference if authenticated
    if (auth()->check()) {
        auth()->user()->update(['preferred_language' => $newLocale]);
    }

    // Store in session for guests
    session(['locale' => $newLocale]);

    return redirect()->back();
})->name('locale.toggle');

// Authentication routes
Auth::routes();

// Webhook routes (no authentication required, rate limited)
Route::prefix('api/webhooks')->name('webhooks.')->middleware('throttle:120,1')->group(function () {
    Route::post('/smsapi/delivery-status', [SmsApiWebhookController::class, 'handleDeliveryStatus'])
        ->name('smsapi.delivery-status');

    Route::post('/smsapi/incoming', [SmsApiIncomingController::class, 'handleIncoming'])
        ->name('smsapi.incoming');
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Booking
    Route::get('/services/{service}/book', [BookingController::class, 'create'])->name('booking.create');

    Route::post('/booking/available-slots', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');

    // Appointments
    Route::get('/my-appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
});


Route::prefix('api')->name('api.')->middleware(['auth'])->group(function () {
    Route::get('/vehicle-types', [VehicleDataController::class, 'vehicleTypes'])->name('vehicle-types');
    Route::get('/car-brands', [VehicleDataController::class, 'brands'])->name('car-brands');
    Route::get('/car-models', [VehicleDataController::class, 'models'])->name('car-models');
    Route::get('/vehicle-years', [VehicleDataController::class, 'years'])->name('vehicle-years');
});
