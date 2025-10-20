<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
// Admin Controllers
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\DatPhongController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PhongController;

// Client Controllers
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\PhongController as ClientPhongController;
use App\Http\Controllers\Client\ContactController as ClientContactController;
use App\Http\Controllers\Client\GioiThieuController as ClientGioiThieuController;
use App\Http\Controllers\Client\TinTucController as ClientTinTucController;
use App\Http\Controllers\Client\ThanhToanController as ClientThanhToanController;

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
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// Google OAuth routes
Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('google.login');
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
    Route::get('/test', function () {
        return view('admin.test');
    })->name('test');

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('users');
    Route::resource('loai_phong', LoaiPhongController::class)->names('loai_phong');
    Route::get('phong/available', [PhongController::class, 'available'])->name('phong.available');
    Route::put('phong/{id}/block', [PhongController::class, 'blockRoom'])->name('phong.block');
    Route::resource('phong', PhongController::class)->names('phong');
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    Route::resource('voucher', VoucherController::class)->names('voucher');
    Route::resource('news', \App\Http\Controllers\Admin\NewsController::class)->names('news');
    Route::prefix('reviews')
        ->name('reviews.')
        ->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::get('/{id}', [CommentController::class, 'show'])->name('show');
            Route::put('/{id}/reply', [CommentController::class, 'reply'])->name('reviews.reply');
            Route::delete('/{id}/reply', [CommentController::class, 'deleteReply'])->name('reviews.reply.delete');
            Route::put('/{id}/toggle', [CommentController::class, 'statusToggle'])->name('toggle');
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
        Route::get('/{id}', [DatPhongController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DatPhongController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DatPhongController::class, 'update'])->name('update');
        Route::delete('/{id}', [DatPhongController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/cancel', [DatPhongController::class, 'showCancelForm'])->name('cancel');
        Route::post('/{id}/cancel', [DatPhongController::class, 'submitCancel'])->name('cancel.submit');
        Route::put('/{id}/block', [DatPhongController::class, 'blockRoom'])->name('block');
    });
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

    Route::get('/thanh-toan/{datPhong}', [\App\Http\Controllers\Client\ThanhToanController::class, 'show'])->name('thanh-toan.show');
    Route::post('/thanh-toan/{datPhong}', [\App\Http\Controllers\Client\ThanhToanController::class, 'store'])->name('thanh-toan.store');
    Route::get('/tin-tuc', [ClientTinTucController::class, 'index'])->name('tintuc');
    Route::get('/tin-tuc/{slug}', [ClientTinTucController::class, 'chitiettintuc'])->name('tintuc.show');
});

// Public impersonation stop (in case admin is impersonating)
Route::middleware('auth')->post(
    '/impersonate/stop',
    [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop']
)->name('impersonate.stop.public');
