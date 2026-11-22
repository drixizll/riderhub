<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\BookingMessageController;
use App\Http\Controllers\BookingRatingController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayMongoWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiderBookingController;
use App\Http\Controllers\RiderLocationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

$dashboardRouteForRole = static fn (?string $role): string => match ($role) {
    'customer' => 'customer.dashboard',
    'rider' => 'rider.dashboard',
    'admin' => 'admin.dashboard',
    default => abort(403),
};

Route::post('/webhooks/paymongo', PayMongoWebhookController::class)->name('webhooks.paymongo');
Route::get('/media/profile-photo/{user}', [ProfileController::class, 'showPhoto'])->name('profile.photo.show');
Route::get('/api/weather', WeatherController::class)->middleware('throttle:60,1')->name('weather.current');
Route::get('/api/route', RouteController::class)->middleware('throttle:60,1')->name('route.directions');
Route::post('/api/assistant/chat', [AssistantController::class, 'chat'])->middleware('throttle:30,1')->name('assistant.chat');

Route::get('/', function () use ($dashboardRouteForRole) {
    if (auth()->check()) {
        return redirect()->route($dashboardRouteForRole(auth()->user()->role));
    }

    return view('welcome');
});

Route::get('/dashboard', function () use ($dashboardRouteForRole) {
    return redirect()->route($dashboardRouteForRole(auth()->user()->role));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [CustomerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/export/pdf', [ReportController::class, 'customerPdf'])->name('bookings.export.pdf');
    Route::get('/bookings/create', [CustomerBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [CustomerBookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/checkout', [CustomerBookingController::class, 'checkout'])->name('bookings.checkout');
    Route::get('/bookings/{booking}', [CustomerBookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}', [CustomerBookingController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{booking}/receipt', [ReportController::class, 'customerReceipt'])->name('bookings.receipt');
    Route::post('/bookings/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/{booking}/location', [CustomerBookingController::class, 'location'])->name('bookings.location');
    Route::post('/bookings/{booking}/rating', [BookingRatingController::class, 'store'])->name('bookings.rating');
});

Route::middleware(['auth', 'verified', 'role:rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/dashboard', [RiderBookingController::class, 'dashboard'])->name('dashboard');
    Route::get('/earnings/export/pdf', [ReportController::class, 'riderPdf'])->name('earnings.export.pdf');
    Route::get('/bookings', [RiderBookingController::class, 'index'])->name('bookings.index');
    Route::get('/requests/poll', [RiderBookingController::class, 'pollRequests'])->name('requests.poll');
    Route::post('/availability', [RiderBookingController::class, 'toggleAvailability'])->name('availability.toggle');
    Route::post('/bookings/{booking}/accept', [RiderBookingController::class, 'accept'])->name('bookings.accept');
    Route::post('/bookings/{booking}/status', [RiderBookingController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/locations', [RiderLocationController::class, 'store'])->name('locations.store');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [AdminDashboardController::class, 'bookings'])->name('bookings.index');
    Route::get('/bookings/export/pdf', [ReportController::class, 'adminPdf'])->name('bookings.export.pdf');
    Route::get('/bookings/export/csv', [ReportController::class, 'adminCsv'])->name('bookings.export.csv');
    Route::post('/bookings/{booking}/assign', [AdminDashboardController::class, 'assignRider'])->name('bookings.assign');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
    Route::patch('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/bookings/{booking}/messages', [BookingMessageController::class, 'index'])->name('bookings.messages.index');
    Route::post('/bookings/{booking}/messages', [BookingMessageController::class, 'store'])->middleware('throttle:60,1')->name('bookings.messages.store');
});

require __DIR__.'/auth.php';
