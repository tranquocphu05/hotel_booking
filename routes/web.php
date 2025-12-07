<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
// Admin Controllers
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\DatPhongController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\YeuCauDoiPhongController;
// Client Controllers
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\PhongController as ClientPhongController;
use App\Http\Controllers\Client\LoaiPhongController as ClientLoaiPhongController;
use App\Http\Controllers\Client\ContactController as ClientContactController;
use App\Http\Controllers\Client\GioiThieuController as ClientGioiThieuController;
use App\Http\Controllers\Client\TinTucController as ClientTinTucController;
use App\Http\Controllers\Client\ThanhToanController as ClientThanhToanController;
use App\Http\Controllers\Client\CommentController as ClientCommentController;
use App\Http\Controllers\Client\VoucherController as ClientVoucherController;
use App\Http\Controllers\Client\NewsletterController as ClientNewsletterController;
use App\Http\Controllers\Client\YeuCauDoiPhongController as ClientYeuCauDoiPhongController;
use App\Http\Controllers\Client\SePayController;
use App\Http\Controllers\Client\SePayTestController;

//


Route::get('/', [ClientDashboardController::class, 'index'])
    ->name('client.home')
    ->middleware([\App\Http\Middleware\AllowClient::class]);


Route::get('/dashboard', function () {
    // Redirect authenticated users to their role dashboard
    $user = Auth::user();
    if ($user && $user->vai_tro === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('client.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('/profile/booking/{id}/cancel', [ProfileController::class, 'cancelBooking'])->name('profile.booking.cancel');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Booking routes - Now booking by room type instead of specific room
Route::get('/booking/{loaiPhongId?}', [BookingController::class, 'showForm'])->name('booking.form')->middleware('auth');
Route::post('/booking', [BookingController::class, 'submit'])->name('booking.submit')->middleware('auth');
Route::post('/booking/available-count', [BookingController::class, 'getAvailableCount'])->name('booking.available_count')->middleware('auth'); // AJAX endpoint

require __DIR__ . '/auth.php';

// Google OAuth routes
Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/register', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogleRegister'])->name('google.register');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

// Test Google Config
Route::get('/test-google-config', function () {
    $config = config('services.google');
    return response()->json([
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect'],
        'env_redirect' => env('GOOGLE_REDIRECT_URI'),
        'expected' => 'http://127.0.0.1:8000/auth/google/callback',
        'match' => $config['redirect'] === 'http://127.0.0.1:8000/auth/google/callback',
    ]);
});

// =======================
// Admin routes
// =======================
Route::prefix('admin')->name('admin.')->middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue');
    Route::get('/test', function () {
        return view('admin.test');
    })->name('test');






    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
    Route::put('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::resource('loai_phong', LoaiPhongController::class)->names('loai_phong');
    Route::put('loai_phong/{id}/toggle-status', [LoaiPhongController::class, 'toggleStatus'])->name('loai_phong.toggle');
    Route::resource('service', ServiceController::class); 
    Route::resource('phong', \App\Http\Controllers\Admin\PhongController::class)->names('phong');
    Route::put('phong/{id}/update-status', [\App\Http\Controllers\Admin\PhongController::class, 'updateStatus'])->name('phong.update-status');
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    Route::get('/invoices/{invoice}/export', [InvoiceController::class, 'export'])->name('invoices.export');
    // Create EXTRA invoice (service-only) from a paid invoice
    // Show form to compose extra invoice (do NOT persist until confirm)
    Route::get('/invoices/{invoice}/create-extra', [InvoiceController::class, 'createExtra'])->name('invoices.create_extra');
    // Store the confirmed extra invoice
    Route::post('/invoices/{invoice}/store-extra', [InvoiceController::class, 'storeExtra'])->name('invoices.store_extra');
    Route::resource('voucher', VoucherController::class)->names('voucher');
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->names('news');
    Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::get('/{id}', [CommentController::class, 'show'])->name('show');
            Route::put('/{id}/toggle', [CommentController::class, 'statusToggle'])->name('toggle');
            Route::put('/{id}/reply', [CommentController::class, 'reply'])->name('reply');
            Route::delete('/{id}/reply', [CommentController::class, 'deleteReply'])->name('reply.delete');
        });
    // impersonation
    Route::post('impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'impersonate'])
        ->name('impersonate');
    Route::post('impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
        ->name('impersonate.stop');

    // Nhóm route cho đặt phòng
    Route::prefix('dat_phong')->name('dat_phong.')->group(function () {
        Route::get('/', [DatPhongController::class, 'index'])->name('index');
        Route::get('/create', [DatPhongController::class, 'create'])->name('create');
        Route::post('/', [DatPhongController::class, 'store'])->name('store');
        Route::post('/available-count', [DatPhongController::class, 'getAvailableCount'])->name('available_count'); // AJAX endpoint
        Route::get('/{id}', [DatPhongController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DatPhongController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DatPhongController::class, 'update'])->name('update');
        Route::put('/{id}/assign-room', [DatPhongController::class, 'assignRoom'])->name('assign_room');
        Route::delete('/{id}', [DatPhongController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/cancel', [DatPhongController::class, 'showCancelForm'])->name('cancel');
        Route::post('/{id}/cancel', [DatPhongController::class, 'submitCancel'])->name('cancel.submit');
        Route::put('/{id}/block', [DatPhongController::class, 'blockRoom'])->name('block');
        // Quick confirm route
        Route::put('/{id}/confirm', [DatPhongController::class, 'quickConfirm'])->name('confirm');
        // Mark as paid
        Route::put('/{id}/mark-paid', [DatPhongController::class, 'markPaid'])->name('mark_paid');
        // Check-in/Check-out
        Route::post('/{id}/checkin', [DatPhongController::class, 'checkin'])->name('checkin');
        Route::post('/{id}/checkout', [DatPhongController::class, 'checkout'])->name('checkout');
    });

    // Booking Services routes
    Route::prefix('booking-services')->name('booking_services.')->group(function () {
        Route::get('/{datPhongId}', [\App\Http\Controllers\Admin\BookingServiceController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\BookingServiceController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\Admin\BookingServiceController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\BookingServiceController::class, 'destroy'])->name('destroy');
    });

    //YeuCauDoiPhong routes
    Route::get('/yeu-cau-doi-phong', [YeuCauDoiPhongController::class, 'index'])
        ->name('yeu_cau_doi_phong.index');

    Route::post('/yeu-cau-doi-phong/{id}/approve', [YeuCauDoiPhongController::class, 'approve'])
        ->name('yeu_cau_doi_phong.approve');

    Route::post('/yeu-cau-doi-phong/{id}/reject', [YeuCauDoiPhongController::class, 'reject'])
        ->name('yeu_cau_doi_phong.reject');
});

// =======================
// Client routes
// =======================

Route::prefix('client')->name('client.')->middleware([\App\Http\Middleware\AllowClient::class])->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/phong', [ClientPhongController::class, 'index'])->name('phong');
    Route::get('/phong/{id}', [ClientPhongController::class, 'show'])->name('phong.show');

    Route::get('/lien-he', [ClientContactController::class, 'index'])->name('lienhe');
    Route::get('/gioi-thieu', [ClientGioiThieuController::class, 'index'])->name('gioithieu');

    // Payment routes
    Route::get('/thanh-toan/{datPhong}', [ClientThanhToanController::class, 'show'])->name('thanh-toan.show');
    Route::post('/thanh-toan/{datPhong}', [ClientThanhToanController::class, 'store'])->name('thanh-toan.store');
    Route::get('/vnpay/payment/{datPhong}', [ClientThanhToanController::class, 'create_vnpay_payment'])->name('vnpay_payment');
    Route::get('/vnpay/return', [ClientThanhToanController::class, 'vnpay_return'])->name('vnpay_return');

    // SePay Payment routes
    Route::get('/sepay/qr/{datPhong}', [SePayController::class, 'showQR'])->name('sepay.qr');
    Route::get('/sepay/status/{datPhong}', [SePayController::class, 'checkStatus'])->name('sepay.status');

    // SePay TEST routes (⚠️ XÓA KHI DEPLOY PRODUCTION!)
    Route::get('/sepay/test', [SePayTestController::class, 'index'])->name('sepay.test.index');
    Route::post('/sepay/test/simulate/{datPhong}', [SePayTestController::class, 'simulatePayment'])->name('sepay.test.simulate');
    Route::get('/tin-tuc', [ClientTinTucController::class, 'index'])->name('tintuc');
    Route::get('/tin-tuc/{slug}', [ClientTinTucController::class, 'chitiettintuc'])->name('tintuc.show');
    Route::get('/voucher', [ClientVoucherController::class, 'getVoucher'])->name('voucher');

    // Newsletter subscription route
    Route::post('/newsletter/subscribe', [ClientNewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

    // Comment routes
    // Route::post('/comment', [ClientCommentController::class, 'store'])->name('comment.store');
    // Route::put('/comment/{id}', [ClientCommentController::class, 'update'])->name('comment.update');
    // Route::delete('/comment/{id}', [ClientCommentController::class, 'destroy'])->name('comment.destroy');
    Route::get('/danh-gia', [ClientCommentController::class, 'index'])->name('comment.index');
    Route::post('/danh-gia', [ClientCommentController::class, 'store'])->name('comment.store');
    Route::get('/danh-gia/{id}/edit', [ClientCommentController::class, 'edit'])->name('comment.edit');
    Route::post('/danh-gia/{id}/update', [ClientCommentController::class, 'update'])->name('comment.update');
    Route::delete('/danh-gia/{id}', [ClientCommentController::class, 'destroy'])->name('comment.destroy');

    // YeuCauDoiPhong routes
    Route::get('/profile/booking/{booking}/doi-phong', [ClientYeuCauDoiPhongController::class, 'create'])
        ->name('yeu_cau_doi_phong.create');

    Route::post('/profile/booking/{booking}/doi-phong', [ClientYeuCauDoiPhongController::class, 'store'])
        ->name('yeu_cau_doi_phong.store');
});

// Public impersonation stop (in case admin is impersonating)
Route::middleware('auth')->post(
    '/impersonate/stop',
    [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop']
)->name('impersonate.stop.public');
