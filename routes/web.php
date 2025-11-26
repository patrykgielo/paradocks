<?php

use App\Http\Controllers\Api\SmsApiIncomingController;
use App\Http\Controllers\Api\SmsApiWebhookController;
use App\Http\Controllers\Api\VehicleDataController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// CMS Content routes
Route::get('/strona/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/aktualnosci/{slug}', [PostController::class, 'show'])->name('post.show');
Route::get('/promocje/{slug}', [PromotionController::class, 'show'])->name('promotion.show');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

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
