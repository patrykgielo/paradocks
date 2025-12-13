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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserVehicleController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Health check endpoint (for CI/CD deployment verification)
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();

        // Check Redis connection
        Cache::store('redis')->get('health-check-probe');

        // Check critical services
        $checks = [
            'database' => DB::connection()->getPdo() !== null,
            'redis' => Cache::store('redis')->connection()->ping(),
        ];

        $allHealthy = collect($checks)->every(fn ($status) => $status === true || $status === 'PONG');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', 'unknown'),
        ], $allHealthy ? 200 : 503);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String(),
        ], 500);
    }
})->name('health');

// CMS Content routes
Route::get('/strona/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/aktualnosci/{slug}', [PostController::class, 'show'])->name('post.show');
Route::get('/promocje/{slug}', [PromotionController::class, 'show'])->name('promotion.show');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

// Service Pages routes (P0: SEO-friendly Polish URLs with rate limiting)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/uslugi', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/uslugi/{service:slug}', [ServiceController::class, 'show'])->name('service.show');
});

// Authentication routes
Auth::routes();

// Password Setup Routes (for admin-created users)
Route::get('/password/setup/{token}', [App\Http\Controllers\Auth\SetPasswordController::class, 'show'])
    ->name('password.setup');
Route::post('/password/setup', [App\Http\Controllers\Auth\SetPasswordController::class, 'store'])
    ->name('password.setup.store')
    ->middleware('throttle:6,1'); // Rate limit: 6 attempts per minute

// Webhook routes (no authentication required, rate limited)
Route::prefix('api/webhooks')->name('webhooks.')->middleware('throttle:120,1')->group(function () {
    Route::post('/smsapi/delivery-status', [SmsApiWebhookController::class, 'handleDeliveryStatus'])
        ->name('smsapi.delivery-status');

    Route::post('/smsapi/incoming', [SmsApiIncomingController::class, 'handleIncoming'])
        ->name('smsapi.incoming');
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Booking (old single-page flow)
    Route::get('/services/{service}/book', [BookingController::class, 'create'])->name('booking.create');
    Route::get('/booking/available-slots', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');

    // Booking Wizard (new multi-step flow)
    Route::get('/booking/step/{step}', [BookingController::class, 'showStep'])->name('booking.step');
    Route::post('/booking/step/{step}', [BookingController::class, 'storeStep'])->name('booking.step.store');

    // Booking AJAX APIs
    Route::post('/booking/save-progress', [BookingController::class, 'saveProgress'])->name('booking.save-progress');
    Route::get('/booking/restore-progress', [BookingController::class, 'restoreProgress'])->name('booking.restore-progress');

    // Calendar availability endpoint (AJAX) - requires auth to match booking wizard access
    Route::get('/booking/unavailable-dates', [BookingController::class, 'getUnavailableDates'])
        ->name('booking.unavailable-dates');

    // Booking confirmation (SECURITY: No ID in URL, uses single-use session token)
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
    Route::get('/booking/confirmation', [BookingController::class, 'showConfirmation'])->name('booking.confirmation');
    Route::get('/booking/ical/{appointment}', [BookingController::class, 'downloadIcal'])->name('booking.ical');

    // Appointments
    Route::get('/my-appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Profile routes
    Route::prefix('moje-konto')->name('profile.')->group(function () {
        // Profile index with grouped list navigation (iOS pattern)
        Route::get('/', [ProfileController::class, 'index'])->name('index');

        // Profile pages
        Route::get('/dane-osobowe', [ProfileController::class, 'personal'])->name('personal');
        Route::get('/pojazd', [ProfileController::class, 'vehicle'])->name('vehicle');
        Route::get('/adres', [ProfileController::class, 'address'])->name('address');
        Route::get('/powiadomienia', [ProfileController::class, 'notifications'])->name('notifications');
        Route::get('/bezpieczenstwo', [ProfileController::class, 'security'])->name('security');

        // Personal Info update
        Route::patch('/dane-osobowe', [ProfileController::class, 'updatePersonalInfo'])->name('personal.update');

        // Email Change
        Route::post('/email/zmien', [ProfileController::class, 'requestEmailChange'])->name('email.change');
        Route::get('/email/potwierdz/{token}', [ProfileController::class, 'confirmEmailChange'])->name('email.confirm');

        // Password
        Route::patch('/haslo', [ProfileController::class, 'changePassword'])->name('password.update');

        // Notifications update
        Route::patch('/powiadomienia/zapisz', [ProfileController::class, 'updateNotifications'])->name('notifications.update');

        // Vehicle (single)
        Route::post('/pojazd/zapisz', [UserVehicleController::class, 'store'])->name('vehicle.store');
        Route::patch('/pojazd/{vehicle}', [UserVehicleController::class, 'update'])->name('vehicle.update');
        Route::delete('/pojazd/{vehicle}', [UserVehicleController::class, 'destroy'])->name('vehicle.destroy');

        // Address (single)
        Route::post('/adres/zapisz', [UserAddressController::class, 'store'])->name('address.store');
        Route::patch('/adres/{address}', [UserAddressController::class, 'update'])->name('address.update');
        Route::delete('/adres/{address}', [UserAddressController::class, 'destroy'])->name('address.destroy');

        // Account Deletion
        Route::post('/usun-konto', [ProfileController::class, 'requestDeletion'])->name('delete.request');
        Route::get('/usun-konto/potwierdz/{token}', [ProfileController::class, 'confirmDeletion'])->name('delete.confirm');
        Route::post('/usun-konto/anuluj', [ProfileController::class, 'cancelDeletion'])->name('delete.cancel');
    });
});

Route::prefix('api')->name('api.')->middleware(['auth'])->group(function () {
    Route::get('/vehicle-types', [VehicleDataController::class, 'vehicleTypes'])->name('vehicle-types');
    Route::get('/car-brands', [VehicleDataController::class, 'brands'])->name('car-brands');
    Route::get('/car-models', [VehicleDataController::class, 'models'])->name('car-models');
    Route::get('/vehicle-years', [VehicleDataController::class, 'years'])->name('vehicle-years');
});
