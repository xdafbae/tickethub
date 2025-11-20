<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\TicketTypeController as AdminTicketTypeController;
use App\Http\Controllers\Admin\SeatMapController as AdminSeatMapController;
use App\Http\Controllers\User\EventBrowseController;
use App\Http\Controllers\User\LandingController;
use App\Models\Event;
use App\Http\Controllers\User\SeatSelectionController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\Admin\PromoController as AdminPromoController;
use App\Http\Controllers\WebhookController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

// Route publik untuk listing event (guest bisa akses)
Route::get('/events', [EventBrowseController::class, 'index'])->name('user.events.index');
// Detail event publik (guest bisa akses)
Route::get('/events/{event}', [EventBrowseController::class, 'show'])->name('user.events.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

// Prefix admin untuk menghindari bentrok dengan route publik /events
Route::middleware(['auth','verified','role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('admin.dashboard');
    Route::get('/blank', function () { return view('admin.blank'); })->name('admin.blank');

    // Resource admin sekarang berada di /admin/events
    Route::resource('events', AdminEventController::class)->names('admin.events');
    Route::resource('ticket-types', AdminTicketTypeController::class)->names('admin.ticket_types');

    Route::get('events/{event}/seat-map', [AdminSeatMapController::class, 'builder'])->name('admin.seat_map.builder');
    Route::post('events/{event}/seat-map', [AdminSeatMapController::class, 'save'])->name('admin.seat_map.save');
    Route::resource('promos', AdminPromoController::class)->names('admin.promos');
});

// Hapus proteksi user untuk /events karena sudah publik di atas
// Route::middleware(['auth','verified','role:user'])->group(function () {
//     Route::get('/events', [EventBrowseController::class, 'index'])->name('user.events.index');
// });

// Rute publik untuk Seat Selection (JSON dan aksi lock)
Route::get('/events/{event}/seat-map', [SeatSelectionController::class, 'map'])->name('user.events.seat.map');
Route::post('/events/{event}/seat-lock', [SeatSelectionController::class, 'lock'])->name('user.events.seat.lock');
Route::post('/events/{event}/seat-unlock', [SeatSelectionController::class, 'unlock'])->name('user.events.seat.unlock');

// Cart & Checkout (User)
Route::prefix('events/{event}')->group(function () {
    Route::get('/cart', [CartController::class, 'cart'])->name('user.cart.show');
    Route::post('/cart', [CartController::class, 'update'])->name('user.cart.update');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('user.checkout.show');
    Route::post('/checkout/confirm', [CartController::class, 'confirm'])->name('user.checkout.confirm');
    // Tambah: apply promo AJAX
    Route::post('/checkout/apply-promo', [CartController::class, 'applyPromo'])->name('user.checkout.apply_promo');
});

// Halaman status pembayaran (di luar prefix event agar mudah di-redirect)
Route::get('/orders/{order}/status', [\App\Http\Controllers\User\CartController::class, 'paymentStatus'])->name('user.payment.status');
Route::post('/orders/{order}/status/check', [\App\Http\Controllers\User\CartController::class, 'checkPaymentStatus'])->name('user.payment.status.check');

// Webhook endpoints (tanpa CSRF)
Route::post('/webhooks/midtrans', [WebhookController::class, 'midtrans'])->name('webhooks.midtrans');

